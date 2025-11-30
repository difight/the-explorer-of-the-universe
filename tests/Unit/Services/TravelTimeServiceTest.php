<?php

use App\Models\StarSystem;
use App\Models\Planet;
use App\Services\TravelTimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTimeService = new TravelTimeService();
});

it('can calculate travel time between planets in the same system', function () {
    $starSystem = StarSystem::factory()->create();
    
    $planet1 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 1.0,
    ]);
    
    $planet2 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 2.0,
    ]);

    $travelTime = $this->travelTimeService->calculateTravelTime($planet1, $planet2);

    expect($travelTime)->toBeInt();
    expect($travelTime)->toBeGreaterThan(0);
    
    // Проверяем, что время путешествия соответствует ожидаемому диапазону
    // Для близких планет в одной системе это должно быть относительно быстро
    expect($travelTime)->toBeLessThan(24); // Меньше 24 часов
});

it('can calculate travel time between planets in different systems', function () {
    $starSystem1 = StarSystem::factory()->create();
    $starSystem2 = StarSystem::factory()->create();
    
    $planet1 = Planet::factory()->create([
        'star_system_id' => $starSystem1->id,
        'orbit_distance' => 1.0,
    ]);
    
    $planet2 = Planet::factory()->create([
        'star_system_id' => $starSystem2->id,
        'orbit_distance' => 1.0,
    ]);

    $travelTime = $this->travelTimeService->calculateTravelTime($planet1, $planet2);

    expect($travelTime)->toBeInt();
    expect($travelTime)->toBeGreaterThan(0);
    
    // Проверяем, что время путешествия между системами больше
    expect($travelTime)->toBeGreaterThan(24); // Больше 24 часов
});

it('returns zero travel time for the same planet', function () {
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
    ]);

    $travelTime = $this->travelTimeService->calculateTravelTime($planet, $planet);

    expect($travelTime)->toBe(0);
});

it('calculates correct travel time based on distance', function () {
    $starSystem = StarSystem::factory()->create();
    
    $planet1 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 1.0,
    ]);
    
    $planet2 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 1.5,
    ]);
    
    $planet3 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 2.0,
    ]);

    $travelTime1 = $this->travelTimeService->calculateTravelTime($planet1, $planet2);
    $travelTime2 = $this->travelTimeService->calculateTravelTime($planet1, $planet3);

    // Время до более далекой планеты должно быть больше
    expect($travelTime2)->toBeGreaterThan($travelTime1);
});

it('handles edge cases with extreme distances', function () {
    $starSystem1 = StarSystem::factory()->create();
    $starSystem2 = StarSystem::factory()->create();
    
    // Создаем планеты с экстремальными расстояниями
    $planet1 = Planet::factory()->create([
        'star_system_id' => $starSystem1->id,
        'orbit_distance' => 0.1, // Очень близко к звезде
    ]);
    
    $planet2 = Planet::factory()->create([
        'star_system_id' => $starSystem2->id,
        'orbit_distance' => 50.0, // Очень далеко от звезды
    ]);

    $travelTime = $this->travelTimeService->calculateTravelTime($planet1, $planet2);

    expect($travelTime)->toBeInt();
    expect($travelTime)->toBeGreaterThan(0);
    
    // Даже для экстремальных расстояний время должно быть в разумных пределах
    expect($travelTime)->toBeLessThan(1000); // Меньше 1000 часов
});

it('is consistent with travel time calculations', function () {
    $starSystem = StarSystem::factory()->create();
    
    $planet1 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 1.0,
    ]);
    
    $planet2 = Planet::factory()->create([
        'star_system_id' => $starSystem->id,
        'orbit_distance' => 2.0,
    ]);

    // Выполняем несколько расчетов
    $travelTimes = [];
    for ($i = 0; $i < 5; $i++) {
        $travelTimes[] = $this->travelTimeService->calculateTravelTime($planet1, $planet2);
    }

    // Проверяем, что все результаты одинаковы
    foreach ($travelTimes as $time) {
        expect($time)->toBe($travelTimes[0]);
    }
});