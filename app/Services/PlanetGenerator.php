<?php

namespace App\Services;

use App\Models\StarSystem;
use App\Models\Planet;

class PlanetGenerator
{
    private array $planetTypes = [
        ['name' => 'barren', 'life_chance' => 0.0, 'resource_bonus' => 1.0],
        ['name' => 'desert', 'life_chance' => 0.001, 'resource_bonus' => 1.2],
        ['name' => 'oceanic', 'life_chance' => 0.01, 'resource_bonus' => 1.5],
        ['name' => 'tundra', 'life_chance' => 0.003, 'resource_bonus' => 1.1],
        ['name' => 'gas_giant', 'life_chance' => 0.0, 'resource_bonus' => 2.0],
        ['name' => 'volcanic', 'life_chance' => 0.0001, 'resource_bonus' => 1.8],
    ];

    private array $specialFeatures = [
        'radioactive', 'magnetic_storms', 'ancient_ruins',
        'quantum_anomaly', 'floating_islands', 'crystal_forests'
    ];

    public function generateForSystem(StarSystem $system): void
    {
        if ($system->is_generated) {
            return;
        }

        // Создаем уникальный seed на основе координат
        $seed = crc32("{$system->coord_x}-{$system->coord_y}-{$system->coord_z}");
        mt_srand($seed);

        $numPlanets = mt_rand(3, 9);

        for ($i = 0; $i < $numPlanets; $i++) {
            $planetType = $this->planetTypes[array_rand($this->planetTypes)];

            $hasLife = mt_rand() / mt_getrandmax() < $planetType['life_chance'];

            $specialFeatures = mt_rand() / mt_getrandmax() < 0.3
                ? [ $this->specialFeatures[array_rand($this->specialFeatures)] ]
                : [];

            Planet::create([
                'star_system_id' => $system->id,
                'tech_name' => "Planet-" . ($i + 1),
                'type' => $planetType['name'],
                'has_life' => $hasLife,
                'size' => mt_rand(1000, 50000),
                'resource_bonus' => $planetType['resource_bonus'] * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4),
                'special_features' => $specialFeatures,
            ]);
        }

        $system->update(['is_generated' => true]);
    }
}
