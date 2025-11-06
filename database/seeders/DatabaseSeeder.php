<?php

namespace Database\Seeders;

use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createStartSystem();
    }

    private function createStartSystem(): void
    {
        // Создаем стартовую систему [0,0,0]
        $solarSystem = StarSystem::firstOrCreate(
            ['coord_x' => 0, 'coord_y' => 0, 'coord_z' => 0],
            [
                'name' => 'Solar System',
                'star_type' => 'G',
                'star_mass' => 1.0,
                'is_generated' => true,
                'is_start_system' => true,
            ]
        );

        // Планеты солнечной системы
        $planets = [
            [
                'tech_name' => 'Mercury',
                'type' => 'barren',
                'has_life' => false,
                'size' => 4879,
                'resource_bonus' => 1.2,
                'orbit_distance' => 1,
                'temperature' => 167,
            ],
            [
                'tech_name' => 'Venus',
                'type' => 'volcanic',
                'has_life' => false,
                'size' => 12104,
                'resource_bonus' => 1.5,
                'orbit_distance' => 2,
                'temperature' => 462,
            ],
            [
                'tech_name' => 'Earth',
                'type' => 'oceanic',
                'has_life' => true,
                'size' => 12742,
                'resource_bonus' => 2.0,
                'orbit_distance' => 3,
                'temperature' => 15,
            ],
            [
                'tech_name' => 'Mars',
                'type' => 'desert',
                'has_life' => false,
                'size' => 6779,
                'resource_bonus' => 1.3,
                'orbit_distance' => 4,
                'temperature' => -63,
            ],
            [
                'tech_name' => 'Jupiter',
                'type' => 'gas_giant',
                'has_life' => false,
                'size' => 139820,
                'resource_bonus' => 2.5,
                'orbit_distance' => 5,
                'temperature' => -108,
            ],
            [
                'tech_name' => 'Saturn',
                'type' => 'gas_giant',
                'has_life' => false,
                'size' => 116460,
                'resource_bonus' => 2.2,
                'special_features' => ['rings'],
                'orbit_distance' => 6,
                'temperature' => -139,
            ],
        ];

        foreach ($planets as $planetData) {
            Planet::firstOrCreate(
                [
                    'star_system_id' => $solarSystem->id,
                    'tech_name' => $planetData['tech_name']
                ],
                $planetData
            );
        }
    }
}
