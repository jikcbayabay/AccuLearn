<?php

namespace App\Services\Mastery;

use App\Enums\MasteryLevel;

class MasteryEngine
{
    /**
     * Compute a mastery score from quiz performance.
     *
     * Formula:
     *   masteryScore = (correctAnswers / totalItems) * 100
     *
     * @param int $correctAnswers Number of correct answers in the attempt.
     * @param int $totalItems     Total questions in the assessment.
     * @return float Score in [0, 100]. Returns 0.0 when totalItems <= 0.
     */
    public function computeMasteryScore(int $correctAnswers, int $totalItems): float
    {
        if ($totalItems <= 0) {
            return 0.0;
        }

        return round(($correctAnswers / $totalItems) * 100, 2);
    }

    /**
     * Classify a mastery score into the string value of MasteryLevel.
     *
     * Thresholds:
     *   >= 85   → 'mastered'
     *   75–84   → 'developing'
     *   <  75   → 'needs_improvement'
     */
    public function classifyMasteryLevel(float $masteryScore): string
    {
        return MasteryLevel::fromScore($masteryScore)->value;
    }

    /**
     * Compute the Guessing Index (GI).
     *
     * Formula:
     *   GI = correctWithLowConfidence / totalCorrect
     *
     * Interpretation: how many "correct" answers were probably guesses.
     * Returns 0.0 when totalCorrect <= 0.
     */
    public function computeGI(int $correctWithLowConfidence, int $totalCorrect): float
    {
        if ($totalCorrect <= 0) {
            return 0.0;
        }

        return round($correctWithLowConfidence / $totalCorrect, 4);
    }

    /**
     * Compute the Confident Misconception Index (CMI).
     *
     * Formula:
     *   CMI = incorrectWithHighConfidence / totalIncorrect
     *
     * Interpretation: how strongly the student holds wrong beliefs.
     * Returns 0.0 when totalIncorrect <= 0.
     */
    public function computeCMI(int $incorrectWithHighConfidence, int $totalIncorrect): float
    {
        if ($totalIncorrect <= 0) {
            return 0.0;
        }

        return round($incorrectWithHighConfidence / $totalIncorrect, 4);
    }
}
