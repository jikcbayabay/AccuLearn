<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Controller;
use App\Models\Competency;
use App\Models\LessonCompletion;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // One query: count completed competencies per module for this user
        $completedByModule = LessonCompletion::where('lesson_completions.user_id', $user->id)
            ->join('competencies', 'lesson_completions.competency_id', '=', 'competencies.id')
            ->groupBy('competencies.module_id')
            ->selectRaw('competencies.module_id, COUNT(*) as completed_count')
            ->pluck('completed_count', 'module_id');

        $modules = Module::withCount('competencies')
            ->orderBy('order')
            ->get(['id', 'title', 'description', 'order', 'moodle_course_id']);

        $payload = $modules->map(function ($m) use ($completedByModule) {
            $total     = (int) $m->competencies_count;
            $completed = (int) $completedByModule->get($m->id, 0);
            $progress  = $total > 0 ? (int) round($completed / $total * 100) : 0;
            $status    = ($total > 0 && $completed === $total) ? 'completed' : 'in-progress';

            return [
                'id'                => $m->id,
                'title'             => $m->title,
                'description'       => $m->description,
                'order'             => $m->order,
                'total_lessons'     => $total,
                'completed_lessons' => $completed,
                'progress'          => $progress,
                'status'            => $status,
            ];
        });

        return response()->json(['modules' => $payload]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $module = Module::with(['competencies' => function ($q) {
            $q->orderBy('order')->with(['learningMaterials' => function ($lm) {
                $lm->select(['id', 'competency_id', 'title', 'type', 'content_url', 'body', 'vark_type']);
            }]);
        }])->find($id);

        if (! $module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        SystemLogController::log($request->user()->id, 'Viewed module', $id);

        return response()->json(['module' => $module]);
    }

    /**
     * GET /student/modules/{id}/quiz
     * Returns the quiz for a module (first competency's assessment).
     * is_correct is intentionally excluded from answers.
     */
    public function quiz(Request $request, int $id): JsonResponse
    {
        $firstCompetency = Competency::where('module_id', $id)
            ->orderBy('order')
            ->first();

        if (! $firstCompetency) {
            return response()->json(['quiz' => null]);
        }

        $assessment = $firstCompetency->assessments()
            ->with(['questions' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('sequence_order')
                  ->with(['answers' => function ($a) {
                      $a->orderBy('sequence_order')
                        ->select(['id', 'question_id', 'answer_text', 'sequence_order']);
                  }]);
            }])
            ->first();

        if (! $assessment || $assessment->questions->isEmpty()) {
            return response()->json(['quiz' => null]);
        }

        return response()->json([
            'quiz' => [
                'id'            => $assessment->id,
                'title'         => $assessment->title,
                'passing_score' => $assessment->passing_score,
                'questions'     => $assessment->questions->map(fn ($q) => [
                    'question_id'   => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type,
                    'answers'       => $q->answers->map(fn ($a) => [
                        'answer_id'      => $a->id,
                        'answer_text'    => $a->answer_text,
                        'sequence_order' => $a->sequence_order,
                    ])->values(),
                ])->values(),
            ],
        ]);
    }
}
