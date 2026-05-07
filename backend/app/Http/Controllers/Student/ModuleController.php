<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $modules = Module::with(['competencies' => function ($q) {
            $q->orderBy('order')->select([
                'id', 'module_id', 'title', 'deped_code', 'order', 'prerequisite_competency_id',
            ]);
        }])
            ->orderBy('order')
            ->get(['id', 'title', 'description', 'order', 'moodle_course_id']);

        SystemLogController::log($request->user()->id, 'Viewed modules');

        return response()->json(['modules' => $modules]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $module = Module::with(['competencies' => function ($q) {
            $q->orderBy('order')->with(['learningMaterials' => function ($lm) {
                $lm->select(['id', 'competency_id', 'title', 'type', 'content_url', 'vark_type']);
            }]);
        }])->find($id);

        if (! $module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        SystemLogController::log($request->user()->id, 'Viewed module', $id);

        return response()->json(['module' => $module]);
    }
}
