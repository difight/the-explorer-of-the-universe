<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем ачивки для типов планет
        $planetTypeAchievements = [
            ['name' => 'gas_giant', 'type' => 'planet_type', 'description' => 'Откройте 5 газовых гигантов', 'icon' => '🪐', 'threshold' => 5],
            ['name' => 'volcanic', 'type' => 'planet_type', 'description' => 'Откройте 3 вулканических мира', 'icon' => '🔥', 'threshold' => 3],
            ['name' => 'oceanic', 'type' => 'planet_type', 'description' => 'Откройте 2 океанических мира', 'icon' => '🌊', 'threshold' => 2],
            ['name' => 'ice_giant', 'type' => 'planet_type', 'description' => 'Откройте 3 ледяных гиганта', 'icon' => '❄️', 'threshold' => 3],
            ['name' => 'jungle', 'type' => 'planet_type', 'description' => 'Откройте 1 джунгли', 'icon' => '🌴', 'threshold' => 1],
        ];
        
        foreach ($planetTypeAchievements as $achievement) {
            \App\Models\AchievementDefinition::firstOrCreate(
                ['name' => $achievement['name'], 'type' => 'planet_type'],
                [
                    'description' => "Откройте {$achievement['threshold']} планет типа {$achievement['name']}",
                    'icon' => $achievement['icon'],
                    'threshold' => $achievement['threshold'],
                    'is_active' => true,
                ]
            );
        }
        
        // Создаем специальные ачивки
        $specialAchievements = [
            ['name' => 'first_system', 'description' => 'Сделайте первое открытие', 'icon' => '🎯'],
            ['name' => 'found_life', 'description' => 'Найдите планету с жизнью', 'icon' => '🌍'],
            ['name' => 'life_in_10_systems', 'description' => 'Найдите жизнь в 10 системах', 'icon' => '⚡'],
            ['name' => '100_systems', 'description' => 'Исследуйте 100 систем', 'icon' => '🏆'],
        ];
        
        foreach ($specialAchievements as $achievement) {
            \App\Models\AchievementDefinition::firstOrCreate(
                ['name' => $achievement['name'], 'type' => 'special'],
                [
                    'description' => $achievement['description'],
                    'icon' => $achievement['icon'],
                    'threshold' => 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
