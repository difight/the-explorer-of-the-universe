<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Спутник создается автоматически через boot метод в модели User

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        Log::info('AuthController@login called', ['request' => $request->all()]);
        Log::info('AuthController@login: Validation started');
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        Log::info('AuthController@login: Validation passed');

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->where('name', 'auth-token')->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);

        Log::info('AuthController@login: Response prepared', ['response' => $response]);
        return $response;

        Log::info('AuthController@login: Response prepared', ['response' => $response]);
        return $response;
    }

    public function logout(Request $request): JsonResponse
    {
        // Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'satellite' => $user->satellite ? [
                    'id' => $user->satellite->id,
                    'name' => $user->satellite->name,
                    'status' => $user->satellite->status,
                    'position' => [
                        'x' => $user->satellite->current_x,
                        'y' => $user->satellite->current_y,
                        'z' => $user->satellite->current_z,
                    ]
                ] : null
            ]
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();

        // Создаем новый токен
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }
}
