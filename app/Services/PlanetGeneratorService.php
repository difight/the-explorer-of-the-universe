<?php

namespace App\Services;

use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Support\Str;

class PlanetGeneratorService
{
    private array $planetTypes = [
        ['name' => 'barren', 'life_chance' => 0.0, 'resource_bonus' => 1.0, 'rarity' => 1.0],
        ['name' => 'desert', 'life_chance' => 0.001, 'resource_bonus' => 1.2, 'rarity' => 1.5],
        ['name' => 'oceanic', 'life_chance' => 0.01, 'resource_bonus' => 1.5, 'rarity' => 2.0],
        ['name' => 'tundra', 'life_chance' => 0.003, 'resource_bonus' => 1.1, 'rarity' => 1.8],
        ['name' => 'gas_giant', 'life_chance' => 0.0, 'resource_bonus' => 2.0, 'rarity' => 1.2],
        ['name' => 'volcanic', 'life_chance' => 0.0001, 'resource_bonus' => 1.8, 'rarity' => 2.5],
        ['name' => 'ice_giant', 'life_chance' => 0.0, 'resource_bonus' => 1.7, 'rarity' => 1.7],
        ['name' => 'jungle', 'life_chance' => 0.02, 'resource_bonus' => 1.6, 'rarity' => 3.0],
        ['name' => 'toxic', 'life_chance' => 0.00001, 'resource_bonus' => 1.4, 'rarity' => 2.2],
    ];

    private array $specialFeatures = [
        'rings', 'strong_magnetic_field', 'volcanic_activity',
        'cryo_volcanoes', 'methane_lakes', 'crystal_formations',
        'ancient_ruins', 'quantum_anomaly', 'floating_islands'
    ];

    private array $starTypes = [
        'M' => ['mass_range' => [0.08, 0.45], 'temp_range' => [2400, 3700]], // Красный карлик
        'K' => ['mass_range' => [0.45, 0.8], 'temp_range' => [3700, 5200]],  // Оранжевый карлик
        'G' => ['mass_range' => [0.8, 1.04], 'temp_range' => [5200, 6000]],  // Желтый карлик
        'F' => ['mass_range' => [1.04, 1.4], 'temp_range' => [6000, 7500]],  // Желто-белый
        'A' => ['mass_range' => [1.4, 2.1], 'temp_range' => [7500, 10000]],  // Белая звезда
        'B' => ['mass_range' => [2.1, 16], 'temp_range' => [10000, 30000]],  // Голубой гигант
    ];

    public function generateForSystem(StarSystem $system): void
    {
        if ($system->is_generated) {
            return;
        }

        // Если это не стартовая система, генерируем случайную звезду
        if (!$system->is_start_system) {
            $this->generateStar($system);
        }

        $seed = $this->createSeed($system->coord_x, $system->coord_y, $system->coord_z);
        mt_srand($seed);

        $numPlanets = mt_rand(3, 9);

        for ($i = 0; $i < $numPlanets; $i++) {
            $this->generatePlanet($system, $i, $seed + $i);
        }

        $system->update(['is_generated' => true]);
    }

    private function generateStar(StarSystem $system): void
    {
        $seed = $this->createSeed($system->coord_x, $system->coord_y, $system->coord_z);
        mt_srand($seed);

        // Веса для типов звезд (красные карлики чаще)
        $starWeights = [70, 25, 4, 0.5, 0.3, 0.2];
        $starTypes = array_keys($this->starTypes);

        $randomValue = mt_rand() / mt_getrandmax() * array_sum($starWeights);
        $cumulative = 0;
        $selectedType = 'M'; // По умолчанию

        foreach ($starWeights as $index => $weight) {
            $cumulative += $weight;
            if ($randomValue <= $cumulative) {
                $selectedType = $starTypes[$index];
                break;
            }
        }

        $starConfig = $this->starTypes[$selectedType];
        $mass = $starConfig['mass_range'][0] + (mt_rand() / mt_getrandmax()) * ($starConfig['mass_range'][1] - $starConfig['mass_range'][0]);

        $system->update([
            'star_type' => $selectedType,
            'star_mass' => round($mass, 2)
        ]);
    }

    private function generatePlanet(StarSystem $system, int $orbitNumber, int $seed): void
    {
        mt_srand($seed);

        $planetType = $this->selectPlanetType();
        $hasLife = (mt_rand() / mt_getrandmax()) < $planetType['life_chance'];

        $specialFeatures = $this->generateSpecialFeatures();

        Planet::create([
            'star_system_id' => $system->id,
            'tech_name' => "Planet-" . ($orbitNumber + 1),
            'type' => $planetType['name'],
            'has_life' => $hasLife,
            'size' => mt_rand(1000, 50000),
            'resource_bonus' => $planetType['resource_bonus'] * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4),
            'special_features' => $specialFeatures,
            'orbit_distance' => $orbitNumber + 1,
            'temperature' => $this->calculateTemperature($system, $orbitNumber),
        ]);
    }

    private function selectPlanetType(): array
    {
        $weights = array_column($this->planetTypes, 'rarity');
        $totalWeight = array_sum($weights);
        $randomValue = mt_rand() / mt_getrandmax() * $totalWeight;

        $cumulative = 0;
        foreach ($this->planetTypes as $type) {
            $cumulative += $type['rarity'];
            if ($randomValue <= $cumulative) {
                return $type;
            }
        }

        return $this->planetTypes[0];
    }

    private function generateSpecialFeatures(): array
    {
        $features = [];
        foreach ($this->specialFeatures as $feature) {
            if ((mt_rand() / mt_getrandmax()) < 0.15) { // 15% шанс на каждую особенность
                $features[] = $feature;
            }
        }
        return $features;
    }

    private function calculateTemperature(StarSystem $system, int $orbitNumber): float
    {
        $baseTemp = $this->starTypes[$system->star_type]['temp_range'][0];
        $distanceFactor = 1 / sqrt($orbitNumber + 1);
        $randomVariation = (mt_rand() / mt_getrandmax() - 0.5) * 100;

        return round($baseTemp * $distanceFactor + $randomVariation, 1);
    }

    private function createSeed(int $x, int $y, int $z): int
    {
        return crc32("{$x}-{$y}-{$z}");
    }
}
