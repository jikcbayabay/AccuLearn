<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Controller;
use App\Models\KnowledgeGap;
use App\Models\LearnerProfile;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $modules = Module::withCount('competencies')
            ->orderBy('order')
            ->get(['id', 'title', 'description', 'order']);

        $modulesPayload = $modules->map(fn ($m) => [
            'id'                => $m->id,
            'title'             => $m->title,
            'description'       => $m->description,
            'order'             => $m->order,
            'competency_count'  => $m->competencies_count,
        ]);

        $stats = (new ProgressController())->index($request)->getData()->stats;

        // Learner model (derived in Tasks 8-9) — surfaced for the dashboard.
        $profile = LearnerProfile::where('user_id', $user->id)->first();
        $profilePayload = $profile ? [
            'learning_pace'        => $profile->learning_pace,
            'avg_mastery'          => (float) $profile->avg_mastery,
            'confidence_alignment' => (float) $profile->confidence_alignment,
            'lessons_completed'    => $profile->lessons_completed,
            'open_gaps'            => $profile->open_gaps,
        ] : null;

        $gaps = KnowledgeGap::with('competency:id,title')
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('last_detected_at')
            ->limit(5)
            ->get()
            ->map(fn ($g) => [
                'competency'  => $g->competency?->title ?? 'Unknown competency',
                'gap_type'    => $g->gap_type,
                'detail'      => $g->detail,
                'occurrences' => $g->occurrences,
            ]);

        SystemLogController::log($user->id, 'Viewed dashboard');

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
            ],
            'stats'           => $stats,
            'modules'         => $modulesPayload,
            'learner_profile' => $profilePayload,
            'knowledge_gaps'  => $gaps,
        ]);
    }
}
