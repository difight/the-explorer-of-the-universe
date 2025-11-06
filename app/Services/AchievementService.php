<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;

class AchievementService
{
    private array $planetTypeAchievements = [
        'gas_giant' => ['name' => 'Газовый гигант', 'icon' => '🪐', 'threshold' => 5],
        'volcanic' => ['name' => 'Вулканический мир', 'icon' => '🔥', 'threshold' => 3],
        'oceanic' => ['name' => 'Океанический исследователь', 'icon' => '🌊', 'threshold' => 2],
        'ice_giant' => ['name' => 'Ледяной пионер', 'icon' => '❄️', 'threshold' => 3],
        'jungle' => ['name' => 'Исследователь джунглей', 'icon' => '🌴', 'threshold' => 1],
    ];

    private array $specialAchievements = [
        'first_discovery' => ['name' => 'Первооткрыватель', 'icon' => '🎯', 'condition' => 'first_system'],
        'found_life' => ['name' => 'Ксенобиолог', 'icon' => '🌍', 'condition' => 'found_life'],
        'lucky_explorer' => ['name' => 'Везунчик', 'icon' => '⚡', 'condition' => 'life_in_10_systems'],
        'galactic_explorer' => ['name' => 'Галактический исследователь', 'icon' => '🏆', 'condition' => '100_systems'],
    ];

    public function checkPlanetTypeAchievements(User $user): void
    {
        $discoveries = $user->discoveries()
            ->with('planet')
            ->approved()
            ->get();

        $planetTypeCounts = [];
        foreach ($discoveries as $discovery) {
            $type = $discovery->planet->type;
            $planetTypeCounts[$type] = ($planetTypeCounts[$type] ?? 0) + 1;
        }

        foreach ($this->planetTypeAchievements as $type => $achievement) {
            $count = $planetTypeCounts[$type] ?? 0;
            if ($count >= $achievement['threshold']) {
                $this->grantAchievement($user, 'planet_type', $achievement['name'], $achievement['icon'], [
                    'planet_type' => $type,
                    'count' => $count
                ]);
            }
        }
    }

    public function checkSpecialAchievements(User $user, string $condition, array $data = []): void
    {
        foreach ($this->specialAchievements as $key => $achievement) {
            if ($achievement['condition'] === $condition) {
                $this->grantAchievement($user, 'special', $achievement['name'], $achievement['icon'], $data);
            }
        }
    }

    private function grantAchievement(User $user, string $type, string $name, string $icon, array $metadata = []): void
    {
        // Проверяем, нет ли уже такого достижения
        $exists = Achievement::where('user_id', $user->id)
            ->where('name', $name)
            ->exists();

        if (!$exists) {
            Achievement::create([
                'user_id' => $user->id,
                'type' => $type,
                'name' => $name,
                'icon' => $icon,
                'achieved_at' => now(),
                'metadata' => $metadata,
            ]);
        }
    }
}
