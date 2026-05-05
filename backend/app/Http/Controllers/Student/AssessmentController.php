<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentSubmitRequest;
use App\Models\Assessment;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    public function show(Assessment $assessment): JsonResponse
    {
        // TODO: include questions/choices fetched from Moodle once integration is live.
        return response()->json($assessment);
    }

    public function submit(AssessmentSubmitRequest $request, Assessment $assessment): JsonResponse
    {
        // TODO:
        //   1. Persist the attempt.
        //   2. Call MasteryEngine to compute score, GI, CMI.
        //   3. Call LearningPathResolver to assign LP.
        //   4. Trigger OpenAIService->generateFeedback(...).
        //   5. Return attempt summary + feedback id.
        return response()->json([
            'attempt_id'    => null,
            'competency_id' => null,
            'score'         => null,
            'passed'        => null,
        ]);
    }
}
