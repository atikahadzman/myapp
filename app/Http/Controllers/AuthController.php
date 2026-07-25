<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => Role::TYPE_READER, // set by default
            'status' => User::STATUS_ACTIVE, // set by default
        ]);

        $generate_token = $user->createToken($request->device_name ?? 'api-token');
        $token = $generate_token->plainTextToken;

        $expires_at = $generate_token->accessToken->created_at
            ->copy()
            ->addMinutes(config('sanctum.expiration'));

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'expires_at' => $expires_at,
        ], 201);
    }

     public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $generate_token = $user->createToken($request->device_name ?? 'api-token');
        $token = $generate_token->plainTextToken;

        $expires_at = $generate_token->accessToken->created_at
            ->copy()
            ->addMinutes(config('sanctum.expiration'));

        return response()->json([
            'token' => $token,
            'user' => $user,
            'expires_at' => $expires_at,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
