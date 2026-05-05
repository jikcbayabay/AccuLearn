<?php

namespace App\Services\Mastery;

use App\Enums\LearningPath;

class LearningPathResolver
{
    /**
     * High-confidence GI threshold above which we treat correctness as guessing.
     */
    public const HIGH_GI_THRESHOLD = 0.30;

    /**
     * Resolve which Learning Path the student should be placed on.
     *
     * Decision tree:
     *   if prerequisiteMet === false   → LP1 (PREREQUISITE_REVIEW)
     *   else if masteryScore < 75      → LP2 (GUIDED_REMEDIATION)
     *   else if masteryScore in [75,85) OR giScore is high
     *                                  → LP3 (REINFORCEMENT)
     *   else                           → LP4 (ADVANCEMENT)
     *
     * @param bool       $prerequisiteMet  True if all prerequisite competencies are mastered.
     * @param float      $masteryScore     Current attempt's mastery score (0–100).
     * @param float|null $giScore          Optional Guessing Index in [0, 1].
     * @param float|null $cmiScore         Optional Confident Misconception Index in [0, 1] (reserved for future tuning).
     */
    public function resolve(
        bool $prerequisiteMet,
        float $masteryScore,
        ?float $giScore = null,
        ?float $cmiScore = null
    ): LearningPath {
        if (! $prerequisiteMet) {
            return LearningPath::PREREQUISITE_REVIEW;
        }

        if ($masteryScore < 75) {
            return LearningPath::GUIDED_REMEDIATION;
        }

        $highGuessing = $giScore !== null && $giScore >= self::HIGH_GI_THRESHOLD;

        if ($masteryScore < 85 || $highGuessing) {
            return LearningPath::REINFORCEMENT;
        }

        return LearningPath::ADVANCEMENT;
    }
}
