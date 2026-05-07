<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Controller;
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

        SystemLogController::log($user->id, 'Viewed dashboard');

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
            ],
            'stats'   => $stats,
            'modules' => $modulesPayload,
        ]);
    }
}
