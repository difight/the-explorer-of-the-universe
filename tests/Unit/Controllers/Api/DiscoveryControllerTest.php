<?php

use App\Models\User;
use App\Models\Planet;
use App\Models\StarSystem;
use App\Models\Discovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('can list user discoveries', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем открытия для пользователя
    Discovery::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    // Выполняем запрос к API
    $response = actingAs($user)->getJson('/api/discoveries');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'planet_id', 'custom_name', 'status', 'discovered_at']
        ],
        'links',
        'meta'
    ]);
    
    // Проверяем, что возвращены все открытия пользователя
    $response->assertJsonCount(3, 'data');
});

it('can show a specific discovery', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем открытие
    $discovery = Discovery::factory()->create([
        'user_id' => $user->id,
    ]);

    // Выполняем запрос к API
    $response = actingAs($user)->getJson("/api/discoveries/{$discovery->id}");
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            'id',
            'planet_id',
            'custom_name',
            'status',
            'discovered_at',
            'planet' => ['id', 'tech_name', 'type', 'has_life', 'size', 'resource_bonus', 'orbit_distance', 'temperature', 'color']
        ]
    ]);
    
    // Проверяем, что возвращены правильные данные
    $response->assertJsonPath('data.id', $discovery->id);
    $response->assertJsonPath('data.planet_id', $discovery->planet_id);
});

it('returns 404 for non-existent discovery', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API для несуществующего открытия
    $response = actingAs($user)->getJson('/api/discoveries/999999');
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});

it('returns 404 for discovery belonging to another user', function () {
    // Создаем двух пользователей
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Создаем открытие для второго пользователя
    $discovery = Discovery::factory()->create([
        'user_id' => $user2->id,
    ]);

    // Выполняем запрос к API для открытия другого пользователя
    $response = actingAs($user1)->getJson("/api/discoveries/{$discovery->id}");
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});

it('requires authentication to access discoveries', function () {
    // Выполняем запрос без аутентификации
    $response = getJson('/api/discoveries');
    
    // Проверяем, что запрос требует аутентификации
    $response->assertStatus(401);
});

it('paginates discoveries correctly', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем 15 открытий
    Discovery::factory()->count(15)->create([
        'user_id' => $user->id,
    ]);

    // Выполняем запрос к API с пагинацией
    $response = actingAs($user)->getJson('/api/discoveries?page=1&per_page=5');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа с пагинацией
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'planet_id', 'custom_name', 'status', 'discovered_at']
        ],
        'links' => ['first', 'last', 'prev', 'next'],
        'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total']
    ]);
    
    // Проверяем, что возвращено правильное количество открытий
    $response->assertJsonCount(5, 'data');
    
    // Проверяем мета-информацию о пагинации
    $response->assertJsonPath('meta.current_page', 1);
    $response->assertJsonPath('meta.per_page', 5);
    $response->assertJsonPath('meta.total', 15);
});