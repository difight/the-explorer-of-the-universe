<?php

use App\Models\Achievement;
use App\Models\User;
use App\Models\Discovery;
use App\Models\Planet;
use App\Models\Satellite;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->achievementRepository = new \App\Repositories\AchievementRepository();
    $this->achievementService = new AchievementService($this->achievementRepository);
});

it('can unlock discovery achievements', function () {
    // Создаем определение достижения для открытий
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'Исследователь',
        'description' => 'Откройте 5 планет',
        'icon' => '🌍',
        'type' => 'discoveries',
        'threshold' => 5,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 4 открытия
    Discovery::factory()->count(4)->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем 5-е открытие
    Discovery::factory()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('can unlock named planet achievements', function () {
    // Создаем определение достижения для названных планет
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'Называтель',
        'description' => 'Назовите 3 планеты',
        'icon' => '🏷️',
        'type' => 'named_planets',
        'threshold' => 3,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 2 открытия с названиями
    Discovery::factory()->count(2)->withCustomName()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем 3-е открытие с названием
    Discovery::factory()->withCustomName()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('can unlock satellite achievements', function () {
    // Создаем определение достижения для отправленных спутников
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'Спутниковый оператор',
        'description' => 'Отправьте 10 спутников',
        'icon' => '🛰️',
        'type' => 'satellites_sent',
        'threshold' => 10,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 9 спутников
    Satellite::factory()->count(9)->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем 10-й спутник
    Satellite::factory()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('does not unlock achievements below threshold', function () {
    // Создаем определение достижения для открытий
    \App\Models\AchievementDefinition::create([
        'name' => 'Исследователь',
        'description' => 'Откройте 5 планет',
        'icon' => '🌍',
        'type' => 'discoveries',
        'threshold' => 5,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем только 3 открытия
    Discovery::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что достижение не разблокировано
    $this->achievementService->checkAllAchievements($user);
    expect($user->achievements)->toHaveCount(0);
});

it('handles multiple achievements', function () {
    // Создаем несколько определений достижений
    $discoveryAchievement = \App\Models\AchievementDefinition::create([
        'name' => 'Исследователь',
        'description' => 'Откройте 5 планет',
        'icon' => '🌍',
        'type' => 'discoveries',
        'threshold' => 5,
        'is_active' => true,
    ]);

    $namedPlanetAchievement = \App\Models\AchievementDefinition::create([
        'name' => 'Называтель',
        'description' => 'Назовите 3 планеты',
        'icon' => '🏷️',
        'type' => 'named_planets',
        'threshold' => 3,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 5 открытий
    Discovery::factory()->count(5)->create([
        'user_id' => $user->id,
    ]);

    // Создаем 3 открытия с названиями
    Discovery::factory()->count(3)->withCustomName()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что оба достижения разблокированы
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(2);
    
it('can unlock planet type achievements', function () {
    // Создаем определение достижения для типа планет
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'Газовый гигант',
        'description' => 'Откройте 5 газовых гигантов',
        'icon' => '🪐',
        'type' => 'planet_type',
        'threshold' => 5,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 4 планеты типа gas_giant
    $gasGiantPlanets = Planet::factory()->count(4)->create(['type' => 'gas_giant']);
    foreach ($gasGiantPlanets as $planet) {
        Discovery::factory()->create([
            'user_id' => $user->id,
            'planet_id' => $planet->id,
            'status' => 'approved',
        ]);
    }

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем 5-ю планету типа gas_giant
    $fifthPlanet = Planet::factory()->create(['type' => 'gas_giant']);
    Discovery::factory()->create([
        'user_id' => $user->id,
        'planet_id' => $fifthPlanet->id,
        'status' => 'approved',
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkPlanetTypeAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});

it('can unlock special achievements', function () {
    // Создаем определение специального достижения
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'first_system',
        'description' => 'Сделайте первое открытие',
        'icon' => '🎯',
        'type' => 'special',
        'threshold' => 1,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Проверяем, что достижение еще не разблокировано
    expect($user->achievements)->toHaveCount(0);

    // Создаем открытие
    Discovery::factory()->create([
        'user_id' => $user->id,
    ]);

    // Проверяем достижения пользователя
    $this->achievementService->checkSpecialAchievements($user, 'first_system');
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
});
    $achievementIds = $user->achievements->pluck('definition_id')->toArray();
    expect($achievementIds)->toContain($discoveryAchievement->id);
    expect($achievementIds)->toContain($namedPlanetAchievement->id);
});

it('does not unlock achievements multiple times', function () {
    // Создаем определение достижения для открытий
    $achievementDefinition = \App\Models\AchievementDefinition::create([
        'name' => 'Исследователь',
        'description' => 'Откройте 5 планет',
        'icon' => '🌍',
        'type' => 'discoveries',
        'threshold' => 5,
        'is_active' => true,
    ]);

    // Создаем пользователя
    $user = User::factory()->create();

    // Создаем 10 открытий (больше порога)
    Discovery::factory()->count(10)->create([
        'user_id' => $user->id,
    ]);

    // Проверяем, что достижение разблокировано только один раз
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
    expect($user->achievements->first()->definition_id)->toBe($achievementDefinition->id);
    
    // Проверяем еще раз, что достижение не разблокируется повторно
    $this->achievementService->checkAllAchievements($user);
    $user->load('achievements');
    expect($user->achievements)->toHaveCount(1);
});

it('handles edge cases with zero counts', function () {
    // Создаем определение достижения для открытий
    \App\Models\AchievementDefinition::create([
        'name' => 'Исследователь',
        'description' => 'Откройте 1 планету',
        'icon' => '🌍',
        'type' => 'discoveries',
        'threshold' => 1,
        'is_active' => true,
    ]);

    // Создаем пользователя без открытий
    $user = User::factory()->create();

    // Проверяем, что достижение не разблокировано
    $this->achievementService->checkAllAchievements($user);
    expect($user->achievements)->toHaveCount(0);
});