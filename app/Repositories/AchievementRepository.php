<?php

namespace App\Repositories;

use App\Models\Achievement;
use App\Models\AchievementDefinition;
use App\Models\User;
use Illuminate\Support\Collection;

class AchievementRepository
{
    public function getActiveDefinitions(): Collection
    {
        return AchievementDefinition::where('is_active', true)->get();
    }
    
    public function getDefinitionById(int $id): ?AchievementDefinition
    {
        return AchievementDefinition::find($id);
    }
    
    public function getDefinitionsByType(string $type): Collection
    {
        return AchievementDefinition::where('type', $type)
            ->where('is_active', true)
            ->get();
    }
    
    public function getUserAchievements(User $user): Collection
    {
        return $user->achievements()->with('definition')->get();
    }
    
    public function getUserAchievement(User $user, int $definitionId): ?Achievement
    {
        return $user->achievements()
            ->where('definition_id', $definitionId)
            ->first();
    }
    
    public function createAchievement(User $user, AchievementDefinition $definition, array $metadata = []): Achievement
    {
        return Achievement::create([
            'user_id' => $user->id,
            'definition_id' => $definition->id,
            'type' => $definition->type,
            'name' => $definition->name,
            'description' => $definition->description,
            'icon' => $definition->icon,
            'threshold' => $definition->threshold,
            'achieved_at' => now(),
            'metadata' => $metadata,
        ]);
    }
    
    public function achievementExists(User $user, int $definitionId): bool
    {
        return Achievement::where('user_id', $user->id)
            ->where('definition_id', $definitionId)
            ->exists();
    }
}