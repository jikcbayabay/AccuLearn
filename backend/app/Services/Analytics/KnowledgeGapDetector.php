<?php

namespace App\Services\Analytics;

use App\Models\AiFeedbackLog;
use App\Models\KnowledgeGap;
use App\Services\Mastery\LearningPathResolver;
use Illuminate\Support\Carbon;

/**
 * Detects per-competency knowledge gaps from a student's attempt history.
 *
 * Evidence-based rules (only what the existing data supports):
 *   • repeated_difficulty     — 3+ attempts on a competency below the
 *                               developing threshold (75%).
 *   • confident_misconception — latest attempt has a high Confidence
 *                               Miscalibration Index (CMI > 0.3) while still
 *                               below mastery (85%): the student is confidently
 *                               wrong.
 *
 * A competency's open gaps are auto-resolved once a mastery-level attempt
 * (>= 85%) is recorded.
 */
class KnowledgeGapDetector
{
    /**
     * Re-evaluate gaps for one (student, competency) after a quiz attempt.
     * Returns the number of currently-open gaps for that competency.
     */
    public function detect(int $userId, int $competencyId): int
    {
        $logs = AiFeedbackLog::where('user_id', $userId)
            ->where('competency_id', $competencyId)
            ->orderByDesc('created_at')
            ->orderByDesc('id') // tiebreaker: same-second attempts order by insertion
            ->get(['id', 'score', 'cmi_score', 'created_at']);

        if ($logs->isEmpty()) {
            return 0;
        }

        $latest = $logs->first();

        // ── Resolution: a mastery-level attempt clears open gaps here ────────
        if ((float) $latest->score >= LearningPathResolver::MASTERY_THRESHOLD) {
            KnowledgeGap::where('user_id', $userId)
                ->where('competency_id', $competencyId)
                ->where('status', 'open')
                ->update(['status' => 'resolved', 'resolved_at' => Carbon::now()]);

            return 0;
        }

        // ── Rule 1: repeated difficulty ─────────────────────────────────────
        $belowDeveloping = $logs->filter(
            fn ($l) => (float) $l->score < LearningPathResolver::DEVELOPING_THRESHOLD
        )->count();

        if ($belowDeveloping >= 3) {
            $this->upsert($userId, $competencyId, 'repeated_difficulty',
                "Scored below {$this->pct(LearningPathResolver::DEVELOPING_THRESHOLD)} on {$belowDeveloping} attempts.",
                $belowDeveloping);
        }

        // ── Rule 2: confident misconception ─────────────────────────────────
        if ((float) $latest->cmi_score > LearningPathResolver::HIGH_CMI_THRESHOLD) {
            $this->upsert($userId, $competencyId, 'confident_misconception',
                'High confidence-miscalibration on the latest attempt (CMI '
                    . number_format((float) $latest->cmi_score, 2) . ').',
                1);
        }

        return KnowledgeGap::where('user_id', $userId)
            ->where('competency_id', $competencyId)
            ->where('status', 'open')
            ->count();
    }

    private function upsert(int $userId, int $competencyId, string $type, string $detail, int $occurrences): void
    {
        KnowledgeGap::updateOrCreate(
            ['user_id' => $userId, 'competency_id' => $competencyId, 'gap_type' => $type],
            [
                'detail'           => $detail,
                'occurrences'      => $occurrences,
                'status'           => 'open',
                'resolved_at'      => null,
                'last_detected_at' => Carbon::now(),
            ],
        );
    }

    private function pct(int|float $v): string
    {
        return ((int) $v) . '%';
    }
}
