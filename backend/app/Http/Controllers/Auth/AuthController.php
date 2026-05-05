<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // TODO: validate credentials, issue Sanctum token, return user payload.
        return response()->json([
            'user'  => null,
            'token' => null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // TODO: revoke current Sanctum token.
        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        // TODO: return $request->user() once auth is wired up.
        return response()->json(['user' => null]);
    }
}
