<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use App\Models\MasteryRecord;
use App\Models\Module;
use App\Models\UsageLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UsageLog::query()
            ->with(['user:id,name,email'])
            ->orderBy('logged_at', 'desc');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->limit(500)->get()->map(fn ($l) => [
            'id'            => $l->id,
            'action'        => $l->action,
            'module_id'     => $l->module_id,
            'competency_id' => $l->competency_id,
            'logged_at'     => $l->logged_at,
            'user'          => $l->user ? [
                'id'    => $l->user->id,
                'name'  => $l->user->name,
                'email' => $l->user->email,
            ] : null,
        ]);

        return response()->json(['logs' => $logs]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_users'     => User::count(),
            'total_students'  => User::where('role', 'student')->count(),
            'total_teachers'  => User::where('role', 'teacher')->count(),
            'total_modules'   => Module::count(),
            'active_sessions' => 0,
            'avg_mastery'     => (float) round(MasteryRecord::avg('mastery_score') ?? 0, 1),
        ]);
    }

    /**
     * GET /admin/lessons-quizzes-mapping
     * Returns a full mapping of every competency lesson to its quiz.
     */
    public function lessonQuizMapping(): JsonResponse
    {
        $modules = Module::with([
            'competencies' => function ($q) {
                $q->orderBy('order')
                  ->with([
                      'learningMaterials' => fn ($lm) => $lm->where('type', 'text')
                          ->select(['id', 'competency_id', 'title']),
                      'assessments' => fn ($a) => $a
                          ->withCount('questions')
                          ->select(['id', 'competency_id', 'title', 'passing_score', 'lesson_id']),
                  ]);
            },
        ])->orderBy('order')->get(['id', 'title', 'order']);

        $mapping = $modules->map(fn ($m) => [
            'module_id'    => $m->id,
            'module_title' => $m->title,
            'competencies' => $m->competencies->map(function ($c) {
                $lesson     = $c->learningMaterials->first();
                $assessment = $c->assessments->first();
                return [
                    'competency_id'    => $c->id,
                    'competency_title' => $c->title,
                    'deped_code'       => $c->deped_code,
                    'lesson' => $lesson ? [
                        'id'    => $lesson->id,
                        'title' => $lesson->title,
                    ] : null,
                    'quiz' => $assessment ? [
                        'id'             => $assessment->id,
                        'title'          => $assessment->title,
                        'passing_score'  => $assessment->passing_score,
                        'question_count' => $assessment->questions_count,
                        'lesson_id'      => $assessment->lesson_id,
                        'linked'         => $assessment->lesson_id === ($lesson?->id),
                    ] : null,
                ];
            })->values(),
        ])->values();

        $totalCompetencies = $modules->sum(fn ($m) => $m->competencies->count());
        $linked = $modules->sum(fn ($m) => $m->competencies->filter(
            fn ($c) => $c->assessments->first()?->lesson_id && $c->learningMaterials->first()
        )->count());

        return response()->json([
            'summary' => [
                'total_modules'      => $modules->count(),
                'total_competencies' => $totalCompetencies,
                'fully_linked'       => $linked,
                'coverage_pct'       => $totalCompetencies > 0 ? round($linked / $totalCompetencies * 100, 1) : 0,
            ],
            'modules' => $mapping,
        ]);
    }

    /**
     * Insert a record into usage_logs. Failures are swallowed silently
     * so log writes never break the calling endpoint.
     */
    public static function log(int $userId, string $action, ?int $moduleId = null, ?int $competencyId = null): void
    {
        try {
            UsageLog::create([
                'user_id'       => $userId,
                'action'        => $action,
                'module_id'     => $moduleId,
                'competency_id' => $competencyId,
                'logged_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            // Best-effort logging; do not propagate.
        }
    }
}
