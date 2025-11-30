<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Albet\SanctumRefresh\Services\TokenIssuer;

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
                'user' => $this->formatUserResponse($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Удаляем связанные refresh токены перед удалением access токенов
        $user->clearRefreshTokens();
        $user->tokens()->where('name', 'api')->delete();

        $token = $this->newToken($user);

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserResponse($user),
                ...$token['data']
            ],
        ]);
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
        return response()->json([
            'data' => $this->formatUserWithSatelliteResponse($request->user())
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $this->extractRefreshToken($request);

        if (!$refreshToken) {
            return response()->json([
                'message' => 'Refresh token not provided',
                'code' => 'missing_refresh_token',
            ], 401);
        }

        // Проверяем refresh токен и получаем пользователя
        $validationResult = $this->validateRefreshTokenAndGetUser($refreshToken);
        if ($validationResult['error']) {
            return response()->json([
                'message' => $validationResult['message'],
                'code' => $validationResult['code'],
            ], 401);
        }

        $user = $validationResult['user'];

        // Генерируем новый токен
        $newToken = \App\Models\RefreshToken::refreshToken($refreshToken);

        // Проверяем, что токен был успешно создан
        if (!$newToken || !isset($newToken->accessToken)) {
            return response()->json([
                'message' => 'Failed to refresh token',
                'code' => 'token_refresh_failed',
            ], 401);
        }

        // Получаем ID нового токена
        $newTokenId = null;
        if (isset($newToken->accessToken) && isset($newToken->accessToken->accessToken)) {
            $newTokenId = $newToken->accessToken->accessToken->id ?? null;
        }

        // Удаляем старые токены
        if ($newTokenId) {
            $this->clearOldTokens($user, $newTokenId);
        }

        return response()->json([
            'message' => 'New token created',
            'data' => [
                'user' => $this->formatUserResponse($user),
                ...$newToken->toArray()
            ]
        ]);
    }

    /**
     * Форматирует данные пользователя для ответа
     */
    private function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    /**
     * Форматирует данные пользователя с информацией о спутнике
     */
    private function formatUserWithSatelliteResponse(User $user): array
    {
        return [
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
        ];
    }

    /**
     * Извлекает refresh токен из запроса
     */
    private function extractRefreshToken(Request $request): ?string
    {
        $refreshToken = $request->get('refresh-token');

        // Если refresh токен не передан в параметрах, пытаемся получить его из заголовка Authorization
        if (!$refreshToken) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
                $refreshToken = $matches[1];
            }
        }

        return $refreshToken;
    }

    /**
     * Проверяет refresh токен и возвращает пользователя
     */
    private function validateRefreshTokenAndGetUser(string $refreshToken): array
    {
        // Проверяем refresh токен на валидность
        if (!User::hasValidRefreshToken($refreshToken)) {
            return [
                'error' => true,
                'message' => 'Invalid or expired refresh token',
                'code' => 'missing_refresh_token',
                'user' => null
            ];
        }

        // Получаем пользователя по refresh токену
        $user = User::byRefreshToken($refreshToken);
        if (!$user) {
            return [
                'error' => true,
                'message' => 'User not found',
                'code' => 'user_not_found',
                'user' => null
            ];
        }

        return [
            'error' => false,
            'message' => '',
            'code' => '',
            'user' => $user
        ];
    }

    /**
     * Удаляет старые токены, кроме указанного
     */
    private function clearOldTokens(User $user, int $exceptTokenId): void
    {
        // Удаляем старые refresh токены, кроме нового
        $user->clearRefreshTokens($exceptTokenId);

        // Удаляем старые access токены, кроме нового
        $user->tokens()->where('name', 'api')->where('id', '!=', $exceptTokenId)->delete();
    }

    private function newToken(User $user) {
        $token = TokenIssuer::issue($user, 'api');

        return [
            'data' => $token->toArray(),
        ];
    }
}
