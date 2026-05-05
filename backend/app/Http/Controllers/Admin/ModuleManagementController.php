<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleManagementController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: list modules ordered by `order`.
        return response()->json([]);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: validate + create module.
        return response()->json(['module' => null], 201);
    }

    public function update(Request $request, Module $module): JsonResponse
    {
        // TODO: validate + update module.
        return response()->json(['module' => $module]);
    }

    public function destroy(Module $module): JsonResponse
    {
        // TODO: delete module.
        return response()->json(['success' => true]);
    }
}
