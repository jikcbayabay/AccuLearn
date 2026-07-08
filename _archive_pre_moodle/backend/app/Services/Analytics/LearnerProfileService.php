<?php

namespace App\Services\Analytics;

use App\Models\AiFeedbackLog;
use App\Models\KnowledgeGap;
use App\Models\LearnerProfile;
use App\Models\LessonCompletion;
use App\Models\MasteryRecord;
use Illuminate\Support\Carbon;

/**
 * Builds/refreshes a student's LearnerProfile from their activity. All values
 * are derived from data the system already records — no new tracking needed.
 */
class LearnerProfileService
{
    public function recompute(int $userId): LearnerProfile
    {
        $mastery = MasteryRecord::where('user_id', $userId)->get(['mastery_score', 'attempt_count']);

        $distinctCompetencies = $mastery->count();
        $attempts             = (int) $mastery->sum('attempt_count');
        $avgMastery           = $distinctCompetencies > 0
            ? round((float) $mastery->avg('mastery_score'), 2)
            : 0.0;

        // Confidence alignment = 1 − average miscalibration (CMI), clamped 0–1.
        $avgCmi = (float) (AiFeedbackLog::where('user_id', $userId)->avg('cmi_score') ?? 0);
        $confidenceAlignment = round(max(0.0, min(1.0, 1.0 - $avgCmi)), 4);

        $lessonsCompleted = LessonCompletion::where('user_id', $userId)->count();
        $openGaps         = KnowledgeGap::where('user_id', $userId)->where('status', 'open')->count();

        $pace = $this->derivePace($avgMastery, $attempts, $distinctCompetencies);

        return LearnerProfile::updateOrCreate(
            ['user_id' => $userId],
            [
                'learning_pace'        => $pace,
                'avg_mastery'          => $avgMastery,
                'confidence_alignment' => $confidenceAlignment,
                'attempts_count'       => $attempts,
                'lessons_completed'    => $lessonsCompleted,
                'open_gaps'            => $openGaps,
                'last_recomputed_at'   => Carbon::now(),
            ],
        );
    }

    /**
     * Learning pace from outcome + effort:
     *   fast   — strong mastery reached with few attempts
     *   slow   — low mastery or many attempts per competency
     *   medium — everything in between
     */
    private function derivePace(float $avgMastery, int $attempts, int $competencies): string
    {
        if ($competencies === 0) {
            return 'medium';
        }

        $attemptsPer = $attempts / $competencies;

        if ($avgMastery >= 85 && $attemptsPer <= 1.5) {
            return 'fast';
        }

        if ($avgMastery < 60 || $attemptsPer >= 3) {
            return 'slow';
        }

        return 'medium';
    }
}
