<?php

use App\Models\User;
use App\Models\Discovery;
use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('can list pending discoveries for admin', function () {
    // Создаем администратора
    $admin = User::factory()->admin()->create();
    
    // Создаем открытия со статусом "pending"
    Discovery::factory()->pending()->count(3)->create();

    // Выполняем запрос к API
    $response = actingAs($admin)->getJson('/api/admin/moderation/pending');
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'user_id', 'planet_id', 'custom_name', 'status', 'discovered_at']
        ],
        'links',
        'meta'
    ]);
    
    // Проверяем, что возвращены все открытия со статусом "pending"
    $response->assertJsonCount(3, 'data');
});

it('cannot list pending discoveries for non-admin', function () {
    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Выполняем запрос к API
    $response = actingAs($user)->getJson('/api/admin/moderation/pending');
    
    // Проверяем ответ об ошибке доступа
    $response->assertStatus(403);
});

it('can approve a discovery', function () {
    // Создаем администратора
    $admin = User::factory()->admin()->create();
    
    // Создаем открытие со статусом "pending"
    $discovery = Discovery::factory()->pending()->create();

    // Выполняем запрос к API для одобрения открытия
    $response = actingAs($admin)->postJson("/api/admin/moderation/approve/{$discovery->id}");
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'message',
        'discovery' => ['id', 'status', 'moderated_at', 'moderated_by']
    ]);
    
    // Проверяем, что открытие было одобрено
    $discovery->refresh();
    expect($discovery->status)->toBe('approved');
    expect($discovery->moderated_by)->toBe($admin->id);
    expect($discovery->moderated_at)->not->toBeNull();
});

it('can reject a discovery', function () {
    // Создаем администратора
    $admin = User::factory()->admin()->create();
    
    // Создаем открытие со статусом "pending"
    $discovery = Discovery::factory()->pending()->create();

    // Выполняем запрос к API для отклонения открытия
    $response = actingAs($admin)->postJson("/api/admin/moderation/reject/{$discovery->id}", [
        'reason' => 'Inappropriate name',
    ]);
    
    // Проверяем успешный ответ
    $response->assertStatus(200);
    
    // Проверяем структуру ответа
    $response->assertJsonStructure([
        'message',
        'discovery' => ['id', 'status', 'rejection_reason', 'moderated_at', 'moderated_by']
    ]);
    
    // Проверяем, что открытие было отклонено
    $discovery->refresh();
    expect($discovery->status)->toBe('rejected');
    expect($discovery->rejection_reason)->toBe('Inappropriate name');
    expect($discovery->moderated_by)->toBe($admin->id);
    expect($discovery->moderated_at)->not->toBeNull();
});

it('cannot moderate discovery for non-admin', function () {
    // Создаем обычного пользователя
    $user = User::factory()->create();
    
    // Создаем открытие со статусом "pending"
    $discovery = Discovery::factory()->pending()->create();

    // Пытаемся одобрить открытие обычным пользователем
    $response = actingAs($user)->postJson("/api/admin/moderation/approve/{$discovery->id}");
    
    // Проверяем ответ об ошибке доступа
    $response->assertStatus(403);
});

it('returns 404 for non-existent discovery', function () {
    // Создаем администратора
    $admin = User::factory()->admin()->create();
    
    // Выполняем запрос к API для несуществующего открытия
    $response = actingAs($admin)->postJson('/api/admin/moderation/approve/999999');
    
    // Проверяем ответ 404
    $response->assertStatus(404);
});

it('requires authentication to access moderation', function () {
    // Выполняем запрос без аутентификации
    $response = getJson('/api/admin/moderation/pending');
    
    // Проверяем, что запрос требует аутентификации
    $response->assertStatus(401);
});