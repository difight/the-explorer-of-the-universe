<?php

use App\Models\StarSystem;
use App\Services\PlanetGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->planetGeneratorService = new PlanetGeneratorService();
});

it('can generate planets for a star system', function () {
    $starSystem = StarSystem::factory()->create([
        'name' => 'Solar System',
        'type' => 'G-type',
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($starSystem);

    // Проверяем, что были созданы планеты
    assertDatabaseCount('planets', 8);
    
    // Проверяем, что у всех планет есть правильная звездная система
    assertDatabaseHas('planets', [
        'star_system_id' => $starSystem->id,
    ]);
    
    // Проверяем, что у планет есть правильные типы
    $planetTypes = ['rocky', 'gas_giant', 'ice_giant', 'terrestrial', 'ocean', 'desert', 'volcanic', 'frozen'];
    foreach ($planetTypes as $type) {
        assertDatabaseHas('planets', [
            'type' => $type,
        ]);
    }
});

it('generates correct planet attributes based on star type', function () {
    $starSystem = StarSystem::factory()->create([
        'type' => 'G-type', // Our sun
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($starSystem);

    // Проверяем, что планеты в зоне обитаемости имеют возможность жизни
    $habitableZonePlanets = $starSystem->planets()
        ->where('orbit_distance', '>=', 0.8)
        ->where('orbit_distance', '<=', 1.5)
        ->get();

    foreach ($habitableZonePlanets as $planet) {
        // Для планет в зоне обитаемости с типом terrestrial или ocean есть шанс на жизнь
        if (in_array($planet->type, ['terrestrial', 'ocean'])) {
            expect($planet->has_life)->toBeBool();
        }
    }
});

it('generates correct planet attributes for different star types', function () {
    // Тест для красного карлика
    $redDwarfSystem = StarSystem::factory()->create([
        'type' => 'M-type',
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($redDwarfSystem);

    // Проверяем, что планеты ближе к красному карлику
    $closePlanets = $redDwarfSystem->planets()
        ->where('orbit_distance', '<=', 1.0)
        ->count();

    expect($closePlanets)->toBeGreaterThan(0);

    // Тест для голубого гиганта
    $blueGiantSystem = StarSystem::factory()->create([
        'type' => 'O-type',
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($blueGiantSystem);

    // Проверяем, что планеты дальше от голубого гиганта
    $distantPlanets = $blueGiantSystem->planets()
        ->where('orbit_distance', '>=', 5.0)
        ->count();

    expect($distantPlanets)->toBeGreaterThan(0);
});

it('generates correct planet sizes and temperatures', function () {
    $starSystem = StarSystem::factory()->create([
        'type' => 'G-type',
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($starSystem);

    $planets = $starSystem->planets;

    foreach ($planets as $planet) {
        // Проверяем, что размер планеты в допустимом диапазоне
        expect($planet->size)->toBeGreaterThanOrEqual(1000);
        expect($planet->size)->toBeLessThanOrEqual(100000);

        // Проверяем, что температура планеты в допустимом диапазоне
        expect($planet->temperature)->toBeGreaterThanOrEqual(-250);
        expect($planet->temperature)->toBeLessThanOrEqual(500);
    }
});

it('generates correct resource bonuses', function () {
    $starSystem = StarSystem::factory()->create([
        'type' => 'G-type',
    ]);

    $this->planetGeneratorService->generatePlanetsForSystem($starSystem);

    $planets = $starSystem->planets;

    foreach ($planets as $planet) {
        // Проверяем, что бонус ресурсов в допустимом диапазоне
        expect($planet->resource_bonus)->toBeGreaterThanOrEqual(0);
        expect($planet->resource_bonus)->toBeLessThanOrEqual(100);
    }
});