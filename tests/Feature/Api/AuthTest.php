<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

test('user can register', function () {
    $response = postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'access_token',
                'token_type',
            ]
        ]);

    expect(DB::table('users')->where('email', 'test@example.com')->exists())->toBeTrue();
});

test('user cannot register with invalid data', function () {
    $response = postJson('/api/auth/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '1234',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'error',
            'messages'
        ]);
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'access_token',
                'access_token_expires_at',
                'refresh_token',
                'refresh_token_expires_at',
            ]
        ]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
});

test('authenticated user can get their data', function () {
    $user = User::factory()->createOne();

    $response = actingAs($user, 'sanctum')->getJson('/api/auth/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'created_at'],
                'satellite'
            ]
        ]);
});

test('user can logout', function () {
    $user = User::factory()->createOne();

    $response = actingAs($user, 'sanctum')->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully logged out']);
});

test('user can refresh token', function () {
    $user = User::factory()->create();

    // Сначала логинимся, чтобы получить токены
    $loginResponse = postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $loginData = $loginResponse->json('data');
    $refreshToken = $loginData['refresh_token'];

    // Теперь пробуем обновить токен
    $response = postJson('/api/auth/refresh', [], [
        'Authorization' => 'Bearer ' . $refreshToken
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'access_token',
                'access_token_expires_at',
                'refresh_token',
                'refresh_token_expires_at',
            ]
        ]);
});

test('user cannot refresh token with invalid token', function () {
    $response = postJson('/api/auth/refresh', [], [
        'Authorization' => 'Bearer invalid-token'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid or expired refresh token',
            'code' => 'missing_refresh_token',
        ]);
});
