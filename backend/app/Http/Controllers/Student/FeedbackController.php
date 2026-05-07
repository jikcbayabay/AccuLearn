<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function show(Request $request, int $competencyId): JsonResponse
    {
        // TODO: return the latest AiFeedbackLog for this user + competency.
        return response()->json([
            'competency_id'    => $competencyId,
            'feedback_text'    => null,
            'error_pattern'    => null,
            'lp_assigned'      => null,
            'gi_score'         => null,
            'cmi_score'        => null,
        ]);
    }
}
