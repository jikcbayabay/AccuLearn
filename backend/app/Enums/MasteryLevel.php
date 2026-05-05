<?php

namespace App\Enums;

enum MasteryLevel: string
{
    case MASTERED          = 'mastered';           // >= 85%
    case DEVELOPING        = 'developing';         // 75% - 84%
    case NEEDS_IMPROVEMENT = 'needs_improvement';  // < 75%

    public const MASTERED_MIN   = 85;
    public const DEVELOPING_MIN = 75;

    public static function fromScore(float $score): self
    {
        if ($score >= self::MASTERED_MIN)   return self::MASTERED;
        if ($score >= self::DEVELOPING_MIN) return self::DEVELOPING;
        return self::NEEDS_IMPROVEMENT;
    }
}
