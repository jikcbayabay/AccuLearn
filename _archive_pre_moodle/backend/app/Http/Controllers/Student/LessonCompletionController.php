<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LessonCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonCompletionController extends Controller
{
    /**
     * GET /student/modules/{moduleId}/completions
     * Returns all competency IDs the authenticated user has completed in a module.
     */
    public function forModule(Request $request, int $moduleId): JsonResponse
    {
        $completedIds = LessonCompletion::where('user_id', $request->user()->id)
            ->whereHas('competency', fn ($q) => $q->where('module_id', $moduleId))
            ->pluck('competency_id');

        return response()->json(['completed_ids' => $completedIds]);
    }

    /**
     * POST /student/competencies/{competencyId}/complete
     * Marks a lesson (competency) as complete for the authenticated user.
     * Safe to call multiple times — uses upsert.
     */
    public function complete(Request $request, int $competencyId): JsonResponse
    {
        LessonCompletion::updateOrCreate(
            ['user_id' => $request->user()->id, 'competency_id' => $competencyId]
        );

        return response()->json(['completed' => true]);
    }
}
