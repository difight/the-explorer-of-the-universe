<?php

use App\Models\User;
use App\Models\Discovery;
use App\Models\Planet;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->achievementRepository = new \App\Repositories\AchievementRepository();
    $this->achievementService = new AchievementService($this->achievementRepository);
});

it('can unlock special achievements', function () {
    // Создаем определение специального достижения
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'first_system',
        'description' => 'Сделайте первое открытие',
        'icon' => '🎯',
        'type' => 'special',
        'threshold' => 1,
        'is_active' => true
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем открытие
    Discovery::factory()->create([
        'user_id' => $user->id
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkSpecialAchievements($user, 'first_system');
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('can unlock planet type achievements', function () {
    // Создаем определение достижения для типа планет
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'gas_giant',
        'description' => 'Откройте 5 газовых гигантов',
        'icon' => '🪐',
        'type' => 'planet_type',
        'threshold' => 5,
        'is_active' => true
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 4 планеты типа gas_giant
    $gasGiantPlanets = Planet::factory()->count(4)->create(['type' => 'gas_giant']);
    foreach ($gasGiantPlanets as $planet) {
        Discovery::factory()->create([
            'user_id' => $user->id,
            'planet_id' => $planet->id,
            'status' => 'approved'
        ]);
    }

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем 5-ю планету типа gas_giant
    $fifthPlanet = Planet::factory()->create(['type' => 'gas_giant']);
    Discovery::factory()->create([
        'user_id' => $user->id,
        'planet_id' => $fifthPlanet->id,
        'status' => 'approved'
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkPlanetTypeAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('does not unlock achievements multiple times', function () {
    // Создаем определение специального достижения
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'first_system',
        'description' => 'Сделайте первое открытие',
        'icon' => '🎯',
        'type' => 'special',
        'threshold' => 1,
        'is_active' => true
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем открытие
    Discovery::factory()->create([
        'user_id' => $user->id
    ]);


it('handles multiple achievements', function () {
    // Создаем несколько определений достижений
    $firstSystemAchievement = \App\Models\AchievementDefinition::create([
        'name' => 'first_system',
        'description' => 'Сделайте первое открытие',
        'icon' => '🎯',
        'type' => 'special',
        'threshold' => 1,
        'is_active' => true
    ]);

    $gasGiantAchievement = \App\Models\AchievementDefinition::create([
        'name' => 'gas_giant',
        'description' => 'Откройте 5 газовых гигантов',
        'icon' => '🪐',
        'type' => 'planet_type',
        'threshold' => 5,
        'is_active' => true
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем открытие
    Discovery::factory()->create([
        'user_id' => $user->id
    ]);

    // Создаем 5 планет типа gas_giant
    $gasGiantPlanets = Planet::factory()->count(5)->create(['type' => 'gas_giant']);
    foreach ($gasGiantPlanets as $planet) {
        Discovery::factory()->create([
            'user_id' => $user->id,
            'planet_id' => $planet->id,
            'status' => 'approved'
        ]);
    }

    // Проверяем, что оба достижения разблокированы
    $this->achievementService->checkSpecialAchievements($user, 'first_system');
    $this->achievementService->checkPlanetTypeAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(2);
    
    $achievementIds = $user->achievements->pluck('definition_id')->toArray();
    expect($achievementIds)->toContain($firstSystemAchievement->id);
    expect($achievementIds)->toContain($gasGiantAchievement->id);
});

it('does not unlock achievements below threshold', function () {
    // Создаем определение достижения для типа планет
    \App\Models\AchievementDefinition::create([
        'name' => 'gas_giant',
        'description' => 'Откройте 5 газовых гигантов',
        'icon' => '🪐',
        'type' => 'planet_type',
        'threshold' => 5,
        'is_active' => true
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем только 3 планеты типа gas_giant
    $gasGiantPlanets = Planet::factory()->count(3)->create(['type' => 'gas_giant']);
    foreach ($gasGiantPlanets as $planet) {
        Discovery::factory()->create([
            'user_id' => $user->id,
            'planet_id' => $planet->id,
            'status' => 'approved'
        ]);
    }

    // Проверяем, что достижение не разблокировано
    $this->achievementService->checkPlanetTypeAchievements($user);
    expect($user->achievements)->toHaveCount(0);
});

it('handles edge cases with zero counts', function () {
    // Создаем определение достижения для типа планет
    \App\Models\AchievementDefinition::create([
        'name' => 'gas_giant',
        'description' => 'Откройте 1 газового гиганта',
        'icon' => '🪐',
        'type' => 'planet_type',
        'threshold' => 1,
        'is_active' => true
    ]);

    // Создаем пользователя без открытий
    $user = User::factory()->create();

    // Проверяем, что достижение не разблокировано
    $this->achievementService->checkPlanetTypeAchievements($user);
    expect($user->achievements)->toHaveCount(0);
});
    // Проверяем, что достижение разблокировано только один раз
    $this->achievementService->checkSpecialAchievements($user, 'first_system');
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
    
    // Проверяем еще раз, что достижение не разблокируется повторно
    $this->achievementService->checkSpecialAchievements($user, 'first_system');
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
});