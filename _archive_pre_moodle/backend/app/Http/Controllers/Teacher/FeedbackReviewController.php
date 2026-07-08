<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AiFeedbackLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = AiFeedbackLog::with(['user:id,name,email', 'competency.module', 'assessment'])
            ->latest()
            ->get();

        $feedback = $logs->map(fn ($log) => [
            'id'          => $log->id,
            'studentName' => $log->user?->name ?? 'Unknown',
            'module'      => $log->competency?->module?->title ?? '—',
            'quiz'        => $log->assessment?->title ?? '—',
            'score'       => (int) $log->correct_count,
            'total'       => (int) $log->total_questions,
            'summary'     => $log->feedback_text ?? '',
            'status'      => $log->status,
            'mistakes'    => $log->mistakes ?? [],
            'suggestions' => $log->suggestions ?? [],
            'gi'          => (float) $log->gi_score,
            'cmi'         => (float) $log->cmi_score,
            'generatedAt' => $log->created_at?->diffForHumans() ?? '',
        ])->values();

        return response()->json(['feedback' => $feedback]);
    }

    public function approve(AiFeedbackLog $feedback): JsonResponse
    {
        $feedback->update(['status' => 'approved']);
        return response()->json(['success' => true, 'feedback_id' => $feedback->id]);
    }
}
