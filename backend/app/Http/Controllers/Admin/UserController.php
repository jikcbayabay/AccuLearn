<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function overview(): JsonResponse
    {
        // TODO: return high-level admin stats (users, modules, events).
        return response()->json([
            'total_users'     => 0,
            'total_modules'   => 0,
            'events_last_24h' => 0,
        ]);
    }

    public function index(): JsonResponse
    {
        // TODO: return a paginated user list.
        return response()->json([]);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: validate + create a new user; hash password; assign role.
        return response()->json(['user' => null], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // TODO: update fields permitted by admin.
        return response()->json(['user' => $user]);
    }

    public function destroy(User $user): JsonResponse
    {
        // TODO: soft-delete or remove the user safely.
        return response()->json(['success' => true]);
    }
}
