<?php

use App\Models\User;
use App\Models\Planet;
use App\Models\StarSystem;
use App\Models\Discovery;
use App\Services\NameModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('can name a planet', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);
    
    // Создаем открытие планеты пользователем без имени
    $discovery = Discovery::factory()->withoutCustomName()->create([
        'user_id' => $user->id,
        'planet_id' => $planet->id,
    ]);

    // Выполняем запрос к API для именования планеты
    $response = actingAs($user)->postJson('/api/planet-name', [
        'discovery_id' => $discovery->id,
        'name' => 'New Planet Name',
    ]);
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'message',
        'discovery' => ['id', 'custom_name', 'status']
    ]);
    
    // Проверяем, что имя было установлено
    $discovery->refresh();
    expect($discovery->custom_name)->toBe('New Planet Name');
    expect($discovery->status)->toBe('pending');
});

it('cannot name a planet with profanity', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);
    
    // Создаем открытие планеты пользователем без имени
    $discovery = Discovery::factory()->withoutCustomName()->create([
        'user_id' => $user->id,
        'planet_id' => $planet->id,
    ]);

    // Выполняем запрос к API для именования планеты нецензурным словом
    $response = actingAs($user)->postJson('/api/planet-name', [
        'discovery_id' => $discovery->id,
        'name' => 'BadWord', // Предполагаем, что это нецензурное слово
    ]);
    
    // Проверяем ответ об ошибке
    $response->assertStatus(400);
    
    // Проверяем сообщение об ошибке
    $response->assertJsonPath('message', 'Name contains prohibited content');
});

it('cannot name a planet that does not belong to user', function () {
    // Создаем двух пользователей
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);
    
    // Создаем открытие планеты вторым пользователем
    $discovery = Discovery::factory()->withoutCustomName()->create([
        'user_id' => $user2->id,
        'planet_id' => $planet->id,
    ]);

    // Пытаемся именовать планету первым пользователем
    $response = actingAs($user1)->postJson('/api/planet-name', [
        'discovery_id' => $discovery->id,
        'name' => 'New Planet Name',
    ]);
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});

it('cannot name a planet that already has a name', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);
    
    // Создаем открытие планеты с именем
    $discovery = Discovery::factory()->withCustomName()->create([
        'user_id' => $user->id,
        'planet_id' => $planet->id,
    ]);

    // Пытаемся переименовать планету
    $response = actingAs($user)->postJson('/api/planet-name', [
        'discovery_id' => $discovery->id,
        'name' => 'Another Name',
    ]);
    
    // Проверяем ответ об ошибке
    $response->assertStatus(400);
    
    // Проверяем сообщение об ошибке
    $response->assertJsonPath('message', 'Planet already has a name');
});

it('requires authentication to name a planet', function () {
    // Создаем пользователя
    $user = User::factory()->create();
    
    // Создаем звездную систему и планету
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);
    
    // Создаем открытие планеты пользователем без имени
    $discovery = Discovery::factory()->withoutCustomName()->create([
        'user_id' => $user->id,
        'planet_id' => $planet->id,
    ]);

    // Выполняем запрос без аутентификации
    $response = postJson('/api/planet-name', [
        'discovery_id' => $discovery->id,
        'name' => 'New Planet Name',
    ]);
    
    // Проверяем, что запрос требует аутентификации
    $response->assertStatus(401);
});

it('returns 404 for non-existent discovery', function () {
    // Создаем пользователя
    $user = User::factory()->create();

    // Выполняем запрос к API для несуществующего открытия
    $response = actingAs($user)->postJson('/api/planet-name', [
        'discovery_id' => 999999,
        'name' => 'New Planet Name',
    ]);
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});