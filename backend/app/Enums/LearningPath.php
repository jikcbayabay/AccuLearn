<?php

namespace App\Enums;

enum LearningPath: int
{
    case PREREQUISITE_REVIEW = 1;
    case GUIDED_REMEDIATION  = 2;
    case REINFORCEMENT       = 3;
    case ADVANCEMENT         = 4;
}
