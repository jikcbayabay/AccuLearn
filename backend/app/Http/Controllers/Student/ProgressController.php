<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $modules = Module::with([
            'competencies' => fn ($q) => $q->orderBy('order'),
        ])->orderBy('order')->get();

        $masteryByCompetency = $user->masteryRecords()
            ->get()
            ->keyBy('competency_id');

        $latestLpByCompetency = $user->learningTracks()
            ->latest('updated_at')
            ->get()
            ->keyBy('competency_id');

        $totalCompetencies     = 0;
        $masteredCount         = 0;
        $developingCount       = 0;
        $needsImprovementCount = 0;
        $notStartedCount       = 0;
        $masterySum            = 0.0;
        $masteryRowCount       = 0;

        $progress = $modules->map(function ($module) use (
            $masteryByCompetency, $latestLpByCompetency,
            &$totalCompetencies, &$masteredCount, &$developingCount,
            &$needsImprovementCount, &$notStartedCount, &$masterySum, &$masteryRowCount
        ) {
            $competencies = $module->competencies->map(function ($c) use (
                $masteryByCompetency, $latestLpByCompetency,
                &$totalCompetencies, &$masteredCount, &$developingCount,
                &$needsImprovementCount, &$notStartedCount, &$masterySum, &$masteryRowCount
            ) {
                $totalCompetencies++;
                $record = $masteryByCompetency->get($c->id);
                $lp     = optional($latestLpByCompetency->get($c->id))->lp;

                if (! $record) {
                    $notStartedCount++;
                    return [
                        'id'             => $c->id,
                        'title'          => $c->title,
                        'deped_code'     => $c->deped_code,
                        'mastery_score'  => null,
                        'mastery_level'  => 'not_started',
                        'attempt_count'  => 0,
                        'lp_assigned'    => $lp,
                    ];
                }

                $level = $record->mastery_level instanceof \BackedEnum
                    ? $record->mastery_level->value
                    : (string) $record->mastery_level;

                match ($level) {
                    'mastered'          => $masteredCount++,
                    'developing'        => $developingCount++,
                    'needs_improvement' => $needsImprovementCount++,
                    default             => null,
                };

                $masterySum      += (float) $record->mastery_score;
                $masteryRowCount += 1;

                return [
                    'id'             => $c->id,
                    'title'          => $c->title,
                    'deped_code'     => $c->deped_code,
                    'mastery_score'  => (float) $record->mastery_score,
                    'mastery_level'  => $level,
                    'attempt_count'  => (int) $record->attempt_count,
                    'lp_assigned'    => $lp,
                ];
            });

            return [
                'module_id'    => $module->id,
                'module_title' => $module->title,
                'competencies' => $competencies->values(),
            ];
        });

        return response()->json([
            'stats' => [
                'total_competencies' => $totalCompetencies,
                'mastered'           => $masteredCount,
                'developing'         => $developingCount,
                'needs_improvement'  => $needsImprovementCount,
                'not_started'        => $notStartedCount,
                'average_mastery'    => $masteryRowCount > 0
                    ? round($masterySum / $masteryRowCount, 2)
                    : 0.00,
            ],
            'progress' => $progress,
        ]);
    }
}
