<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsageLog;
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
