<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'role', 'moodle_user_id', 'section', 'active', 'created_at']);

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['users' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'section'  => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create($data);

        return response()->json([
            'user'    => $user->only(['id', 'name', 'email', 'role', 'section', 'active', 'created_at']),
            'message' => 'User created successfully',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data = $request->validate([
            'name'    => ['sometimes', 'required', 'string', 'max:255'],
            'email'   => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'    => ['sometimes', 'required', Rule::in(['student', 'teacher', 'admin'])],
            'section' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return response()->json([
            'user'    => $user->only(['id', 'name', 'email', 'role', 'section', 'active', 'created_at']),
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot deactivate your own account'], 403);
        }

        $user->update(['active' => false]);

        return response()->json(['message' => 'User deactivated successfully']);
    }
}
