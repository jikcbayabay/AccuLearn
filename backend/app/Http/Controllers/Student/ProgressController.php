<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: return MasteryRecord rows for the authenticated student grouped by module.
        return response()->json([
            'overall_mastery' => null,
            'competencies'    => [],
        ]);
    }
}
