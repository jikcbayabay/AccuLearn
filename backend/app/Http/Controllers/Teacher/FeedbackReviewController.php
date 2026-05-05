<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AiFeedbackLog;
use Illuminate\Http\JsonResponse;

class FeedbackReviewController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: list AI feedback entries pending teacher review.
        return response()->json([]);
    }

    public function approve(AiFeedbackLog $feedback): JsonResponse
    {
        // TODO: mark the feedback log as teacher-approved + persist reviewer id.
        return response()->json(['success' => true, 'feedback_id' => $feedback->id]);
    }
}
