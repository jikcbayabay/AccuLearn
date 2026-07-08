<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AiFeedbackLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * GET /student/feedback/{competencyId}
     * Returns the latest AI feedback entry for the authenticated user + competency.
     */
    public function show(Request $request, int $competencyId): JsonResponse
    {
        $log = AiFeedbackLog::where('user_id', $request->user()->id)
            ->where('competency_id', $competencyId)
            ->latest()
            ->first();

        if (! $log) {
            return response()->json(['feedback' => null]);
        }

        return response()->json([
            'feedback' => [
                'id'              => $log->id,
                'competency_id'   => $log->competency_id,
                'quiz_id'         => $log->quiz_id,
                'summary'         => $log->feedback_text,
                'error_pattern'   => $log->error_pattern,
                'lp_assigned'     => $log->lp_assigned ? 'LP' . $log->lp_assigned : null,
                'gi'              => (float) $log->gi_score,
                'cmi'             => (float) $log->cmi_score,
                'score'           => (float) $log->score,
                'total'           => (int) $log->total_questions,
                'correct_count'   => (int) $log->correct_count,
                'status'          => $log->status,
                'mistakes'        => $log->mistakes ?? [],
                'suggestions'     => $log->suggestions ?? [],
                'generated_at'    => $log->created_at,
            ],
        ]);
    }

    /**
     * GET /student/feedback (all feedback for the current student)
     */
    public function index(Request $request): JsonResponse
    {
        $logs = AiFeedbackLog::where('user_id', $request->user()->id)
            ->with(['competency.module'])
            ->latest()
            ->get();

        $items = $logs->map(fn ($log) => [
            'id'            => $log->id,
            'competency_id' => $log->competency_id,
            'quiz_id'       => $log->quiz_id,
            'module'        => $log->competency?->module?->title ?? '—',
            'quiz'          => $log->assessment?->title ?? '—',
            'summary'       => $log->feedback_text,
            'gi'            => (float) $log->gi_score,
            'cmi'           => (float) $log->cmi_score,
            'score'         => (int) $log->correct_count,
            'total'         => (int) $log->total_questions,
            'status'        => $log->status,
            'mistakes'      => $log->mistakes ?? [],
            'suggestions'   => $log->suggestions ?? [],
            'generated_at'  => $log->created_at,
        ]);

        return response()->json(['feedback' => $items]);
    }
}
