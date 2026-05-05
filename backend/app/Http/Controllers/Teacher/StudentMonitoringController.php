<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StudentMonitoringController extends Controller
{
    public function overview(): JsonResponse
    {
        // TODO: aggregate cohort metrics: total students, average mastery, pending feedback count.
        return response()->json([
            'total_students'    => 0,
            'average_mastery'   => 0,
            'pending_feedback'  => 0,
        ]);
    }

    public function index(): JsonResponse
    {
        // TODO: list students with rolled-up mastery + active LP.
        return response()->json([]);
    }

    public function mastery(User $user): JsonResponse
    {
        // TODO: return mastery records broken down by competency for the given student.
        return response()->json([
            'student'      => $user,
            'competencies' => [],
        ]);
    }
}
