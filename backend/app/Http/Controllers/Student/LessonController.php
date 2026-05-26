<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * GET /student/competencies/{id}/lesson-quiz
     * Returns the lesson (text LM) and its associated quiz for a competency.
     */
    public function lessonWithQuiz(Request $request, int $id): JsonResponse
    {
        $competency = Competency::with([
            'learningMaterials' => fn ($q) => $q->select(['id', 'competency_id', 'title', 'type', 'body', 'content_url', 'vark_type']),
        ])->find($id);

        if (! $competency) {
            return response()->json(['message' => 'Competency not found'], 404);
        }

        $lesson = $competency->learningMaterials->firstWhere('type', 'text');

        $assessment = $competency->assessments()
            ->with(['questions' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('sequence_order')
                  ->with(['answers' => function ($a) {
                      $a->orderBy('sequence_order')
                        ->select(['id', 'question_id', 'answer_text', 'sequence_order']);
                  }]);
            }])
            ->whereHas('questions')
            ->first();

        $quiz = null;
        if ($assessment) {
            $quiz = [
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
            ];
        }

        return response()->json([
            'competency' => [
                'id'        => $competency->id,
                'title'     => $competency->title,
                'deped_code'=> $competency->deped_code,
            ],
            'lesson' => $lesson ? [
                'id'          => $lesson->id,
                'title'       => $lesson->title,
                'body'        => $lesson->body,
                'content_url' => $lesson->content_url,
                'vark_type'   => $lesson->vark_type,
            ] : null,
            'quiz' => $quiz,
        ]);
    }
}
