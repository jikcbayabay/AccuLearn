<?php

namespace App\Http\Controllers\Student;

use App\Enums\MasteryLevel;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Controller;
use App\Models\AiFeedbackLog;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\MasteryRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * POST /student/assessments/submit
     *
     * Body: {
     *   quiz_id: int,
     *   answers: [{ question_id: int, answer_id: int }, ...]
     * }
     */
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id'                 => ['required', 'integer'],
            'answers'                 => ['required', 'array', 'min:1'],
            'answers.*.question_id'   => ['required', 'integer'],
            'answers.*.answer_id'     => ['required', 'integer'],
        ]);

        $assessment = Assessment::find($request->quiz_id);
        if (! $assessment) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        // Load all correct answers for this assessment in one query
        $correctMap = AssessmentAnswer::whereHas(
            'question',
            fn ($q) => $q->where('assessment_id', $assessment->id)->where('is_active', true)
        )
        ->where('is_correct', true)
        ->pluck('id', 'question_id'); // [question_id => correct_answer_id]

        // Grade
        $correctCount = 0;
        $results = [];
        $wrongQuestionIds = [];
        foreach ($request->answers as $sub) {
            $isCorrect = isset($correctMap[$sub['question_id']])
                && (int) $correctMap[$sub['question_id']] === (int) $sub['answer_id'];

            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrongQuestionIds[] = $sub['question_id'];
            }

            $results[] = [
                'question_id' => $sub['question_id'],
                'answer_id'   => $sub['answer_id'],
                'is_correct'  => $isCorrect,
            ];
        }

        $total = count($correctMap);
        $score = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0;
        $user  = $request->user();

        // ── Persist mastery record ─────────────────────────────────────────
        $masteryLevel = MasteryLevel::fromScore($score);
        $existing = MasteryRecord::where('user_id', $user->id)
            ->where('competency_id', $assessment->competency_id)
            ->first();

        if ($existing) {
            $existing->update([
                'mastery_score' => $score,
                'mastery_level' => $masteryLevel,
                'attempt_count' => $existing->attempt_count + 1,
            ]);
        } else {
            MasteryRecord::create([
                'user_id'       => $user->id,
                'competency_id' => $assessment->competency_id,
                'mastery_score' => $score,
                'mastery_level' => $masteryLevel,
                'attempt_count' => 1,
            ]);
        }

        // ── Generate mock AI feedback ──────────────────────────────────────
        $this->generateFeedback($user->id, $assessment, $score, $correctCount, $total, $wrongQuestionIds);

        // ── Activity log ───────────────────────────────────────────────────
        SystemLogController::log($user->id, 'Submitted quiz', null, $assessment->competency_id);

        return response()->json([
            'quiz_id'         => $assessment->id,
            'quiz_title'      => $assessment->title,
            'score'           => $score,
            'passed'          => $score >= $assessment->passing_score,
            'passing_score'   => $assessment->passing_score,
            'correct_count'   => $correctCount,
            'total_questions' => $total,
            'results'         => $results,
        ]);
    }

    private function generateFeedback(
        int $userId, Assessment $assessment,
        float $score, int $correctCount, int $total, array $wrongQuestionIds
    ): void {
        try {
            $wrongCount = count($wrongQuestionIds);
            $pct        = $score;

            // Build mistake topics from wrong question text (first 3)
            $mistakes = [];
            if ($wrongCount > 0) {
                $wrongTexts = \App\Models\AssessmentQuestion::whereIn('id', array_slice($wrongQuestionIds, 0, 3))
                    ->pluck('question_text');
                foreach ($wrongTexts as $txt) {
                    // Shorten to a brief label
                    $mistakes[] = mb_substr($txt, 0, 80) . (mb_strlen($txt) > 80 ? '…' : '');
                }
            }

            $suggestions = match (true) {
                $pct >= 85 => [
                    'Review edge cases in this topic to reinforce mastery.',
                    'Try applying concepts to novel Philippine business scenarios.',
                ],
                $pct >= 75 => [
                    'Re-read the lesson section covering your missed items.',
                    'Practice additional examples before the next attempt.',
                ],
                default => [
                    'Go back to the full lesson reading and take notes.',
                    'Focus on understanding key definitions before retaking.',
                    'Consider asking your teacher for clarification on missed concepts.',
                ],
            };

            $summary = match (true) {
                $pct >= 85 => "Excellent work! You answered {$correctCount} out of {$total} questions correctly ({$pct}%). You have demonstrated strong mastery of this topic.",
                $pct >= 75 => "Good effort! You answered {$correctCount} out of {$total} questions correctly ({$pct}%). You are on track but there are a few areas to review.",
                $pct >= 50 => "Fair attempt. You answered {$correctCount} out of {$total} questions correctly ({$pct}%). Review the lesson material and focus on the areas highlighted below.",
                default    => "You answered {$correctCount} out of {$total} questions correctly ({$pct}%). The lesson content needs to be revisited before your next attempt.",
            };

            // Simulated GI (Guessing Index) and CMI (Confidence Miscalibration Index)
            $gi  = round(max(0, min(1, ($wrongCount / max($total, 1)) * (0.3 + (rand(0, 20) / 100)))), 4);
            $cmi = round(max(0, min(1, abs(($pct / 100) - ($correctCount / max($total, 1))) * (1 + (rand(0, 30) / 100)))), 4);

            $lpRaw = match (true) {
                $pct >= 85 => 1,
                $pct >= 75 => 2,
                $pct >= 50 => 3,
                default    => 4,
            };

            AiFeedbackLog::create([
                'user_id'        => $userId,
                'competency_id'  => $assessment->competency_id,
                'quiz_id'        => $assessment->id,
                'feedback_text'  => $summary,
                'error_pattern'  => $wrongCount > 0 ? 'missed_items' : 'none',
                'lp_assigned'    => $lpRaw,
                'gi_score'       => $gi,
                'cmi_score'      => $cmi,
                'score'          => $score,
                'total_questions'=> $total,
                'correct_count'  => $correctCount,
                'status'         => 'pending',
                'mistakes'       => $mistakes,
                'suggestions'    => $suggestions,
            ]);
        } catch (\Throwable) {
            // Swallow — feedback generation must never break submission
        }
    }
}
