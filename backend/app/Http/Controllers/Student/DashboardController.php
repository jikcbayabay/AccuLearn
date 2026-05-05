<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: aggregate active LP, recommended lessons, recent feedback.
        return response()->json([
            'student_name'         => null,
            'active_lp'            => null,
            'recommended_lessons'  => [],
        ]);
    }
}
