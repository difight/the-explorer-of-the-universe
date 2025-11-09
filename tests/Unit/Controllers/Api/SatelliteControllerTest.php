<?php

use App\Models\User;
use App\Models\Satellite;
use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('can get satellite status', function () {
    // Создаем пользователя и его спутник
    $user = User::factory()->create();
    $satellite = Satellite::factory()->create([
        'user_id' => $user->id,
    ]);

    // Выполняем запрос к API
    $response = actingAs($user)->getJson('/api/satellite/status');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'current_x',
            'current_y',
            'current_z',
            'target_x',
            'target_y',
            'target_z',
            'status',
            'energy',
            'integrity',
            'last_update',
        ]
    ]);
    
    // Проверяем, что возвращены правильные данные
    $response->assertJsonPath('data.id', $satellite->id);
    $response->assertJsonPath('data.name', $satellite->name);
});

it('can send satellite to planet', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);

    // Выполняем запрос к API для отправки спутника
    $response = actingAs($user)->postJson('/api/satellite/send', [
        'planet_id' => $planet->id,
    ]);
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'message',
        'arrival_time',
    ]);
    
    // Проверяем, что спутник получил правильные координаты цели
    $user->satellite->refresh();
    expect($user->satellite->target_x)->toBe($planet->x_coordinate);
    expect($user->satellite->target_y)->toBe($planet->y_coordinate);
    expect($user->satellite->target_z)->toBe($planet->z_coordinate);
});

it('cannot send satellite without enough energy', function () {
    // Создаем пользователя с низким уровнем энергии
    $user = User::factory()->withLowEnergy()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);

    // Выполняем запрос к API для отправки спутника
    $response = actingAs($user)->postJson('/api/satellite/send', [
        'planet_id' => $planet->id,
    ]);
    
    // Проверяем ответ об ошибке
    $response->assertStatus(400);
    
    // Проверяем сообщение об ошибке
    $response->assertJsonPath('message', 'Not enough energy');
});

it('requires authentication to access satellite', function () {
    // Выполняем запрос без аутентификации
    $response = getJson('/api/satellite/status');
    
    // Проверяем, что запрос требует аутентификации
    $response->assertStatus(401);
});

it('returns 404 for non-existent planet', function () {
    // Создаем пользователя
    $user = User::factory()->create();

    // Выполняем запрос к API для отправки спутника к несуществующей планете
    $response = actingAs($user)->postJson('/api/satellite/send', [
        'planet_id' => 999999,
    ]);
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});