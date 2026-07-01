<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
{
    $data = $request->validated();

    // Split name into first/last for Moodle
    $nameParts = explode(' ', trim($data['name']), 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? $nameParts[0];

    // 1. Create user in Laravel DB first
    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => $data['password'],
        'role'     => UserRole::STUDENT,
        'active'   => true,
        'section'  => $data['section'] ?? null,
    ]);

    // 2. Create user in Moodle
    try {
        $moodle      = app(\App\Services\Moodle\MoodleService::class);
        $moodleUsers = $moodle->createUser([
            'username'  => strtolower(str_replace(' ', '.', $data['name'])) . $user->id,
            'password'  => $data['password'],
            'firstname' => $firstName,
            'lastname'  => $lastName,
            'email'     => $data['email'],
        ]);

        // 3. Save Moodle user ID back to Laravel
        $moodleUserId = $moodleUsers[0]['id'] ?? null;
        if ($moodleUserId) {
            $user->update(['moodle_user_id' => $moodleUserId]);
        }

        // 4. Fetch and store their Moodle token
        $moodleToken = $this->fetchMoodleToken($data['email'], $data['password']);
        if ($moodleToken) {
            $user->update(['moodle_token' => $moodleToken]);
        }

    } catch (\RuntimeException $e) {
        // Moodle sync failed — log it but don't block registration
        \Log::warning('Moodle user creation failed: ' . $e->getMessage());
    }

    $token = $user->createToken('api')->plainTextToken;

    return response()->json([
        'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
        ],
        'token' => $token,
    ], 201);
}

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $moodleToken = $this->fetchMoodleToken($credentials['email'], $credentials['password']);
        if ($moodleToken) {
            $user->update(['moodle_token' => $moodleToken]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
            ],
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function fetchMoodleToken(string $username, string $password): ?string
    {
        $response = Http::get(config('services.moodle.url') . '/login/token.php', [
            'username' => $username,
            'password' => $password,
            'service'  => config('services.moodle.service'),
        ]);

        $data = $response->json();

        return $data['token'] ?? null;
    }
}