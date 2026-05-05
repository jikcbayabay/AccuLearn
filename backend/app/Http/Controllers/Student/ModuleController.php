<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: list modules visible to the current student.
        return response()->json([]);
    }

    public function show(Module $module): JsonResponse
    {
        // TODO: return module with competencies and learning materials.
        return response()->json($module);
    }
}
