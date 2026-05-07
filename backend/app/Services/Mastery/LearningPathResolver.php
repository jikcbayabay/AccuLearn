<?php

namespace App\Services\Mastery;

class LearningPathResolver
{
    public const MASTERY_THRESHOLD     = 85;
    public const DEVELOPING_THRESHOLD  = 75;
    public const HIGH_GI_THRESHOLD     = 0.5;
    public const HIGH_CMI_THRESHOLD    = 0.3;

    /**
     * Resolve which Learning Path the student should be placed on.
     *
     * Decision rules (evaluated in order):
     *   1. prerequisite not met            → 1 (PREREQUISITE_REVIEW)
     *   2. masteryScore < 75               → 2 (GUIDED_REMEDIATION)
     *   3. 75 <= masteryScore < 85         → 3 (REINFORCEMENT)
     *   4. masteryScore >= 85, GI > 0.5    → 3 (REINFORCEMENT)  — guessing
     *   5. masteryScore >= 85, CMI > 0.3   → 2 (GUIDED_REMEDIATION) — misconceptions
     *   6. otherwise                       → 4 (ADVANCEMENT)
     */
    public function resolve(
        float $masteryScore,
        bool $prerequisiteMet,
        float $gi,
        float $cmi
    ): int {
        if (! $prerequisiteMet) {
            return 1;
        }

        if ($masteryScore < self::DEVELOPING_THRESHOLD) {
            return 2;
        }

        if ($masteryScore < self::MASTERY_THRESHOLD) {
            return 3;
        }

        if ($gi > self::HIGH_GI_THRESHOLD) {
            return 3;
        }

        if ($cmi > self::HIGH_CMI_THRESHOLD) {
            return 2;
        }

        return 4;
    }
}
