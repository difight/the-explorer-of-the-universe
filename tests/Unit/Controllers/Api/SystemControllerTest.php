<?php

use App\Models\StarSystem;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('can list star systems', function () {
    // Создаем несколько звездных систем
    StarSystem::factory()->count(5)->create();

    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API
    $response = actingAs($user)->getJson('/api/systems');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'type', 'description', 'planets_count']
        ],
        'links',
        'meta'
    ]);
    
    // Проверяем, что возвращены все системы
    $response->assertJsonCount(5, 'data');
});

it('can show a specific star system with planets', function () {
    // Создаем звездную систему с планетами
    $starSystem = StarSystem::factory()->create();
    Planet::factory()->count(3)->create([
        'star_system_id' => $starSystem->id,
    ]);

    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API
    $response = actingAs($user)->getJson("/api/systems/{$starSystem->id}");
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            'id', 
            'name', 
            'type', 
            'description', 
            'planets' => [
                '*' => ['id', 'tech_name', 'type', 'has_life', 'size', 'resource_bonus', 'orbit_distance', 'temperature', 'color']
            ]
        ]
    ]);
    
    // Проверяем, что возвращены все планеты
    $response->assertJsonCount(3, 'data.planets');
});

it('returns 404 for non-existent star system', function () {
    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API для несуществующей системы
    $response = actingAs($user)->getJson('/api/systems/999999');
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});

it('requires authentication to access systems', function () {
    // Выполняем запрос без аутентификации
    $response = getJson('/api/systems');
    
    // Проверяем, что запрос требует аутентификации
    $response->assertStatus(401);
});

it('paginates star systems correctly', function () {
    // Создаем 15 звездных систем
    StarSystem::factory()->count(15)->create();

    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API с пагинацией
    $response = actingAs($user)->getJson('/api/systems?page=1&per_page=5');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа с пагинацией
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'type', 'description', 'planets_count']
        ],
        'links' => ['first', 'last', 'prev', 'next'],
        'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total']
    ]);
    
    // Проверяем, что возвращено правильное количество систем
    $response->assertJsonCount(5, 'data');
    
    // Проверяем мета-информацию о пагинации
    $response->assertJsonPath('meta.current_page', 1);
    $response->assertJsonPath('meta.per_page', 5);
    $response->assertJsonPath('meta.total', 15);
});