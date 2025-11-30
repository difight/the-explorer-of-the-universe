<?php

namespace App\Achievements\Conditions;

use App\Models\User;

class PlanetTypeCondition extends BaseAchievementCondition
{
    public function check(User $user, array $data = []): bool
    {
        $requiredType = $data['planet_type'] ?? null;
        $requiredCount = $data['count'] ?? 0;
        
        if (!$requiredType || $requiredCount <= 0) {
            return false;
        }
        
        $discoveries = $user->discoveries()
            ->with('planet')
            ->approved()
            ->get();
        
        $planetTypeCounts = [];
        foreach ($discoveries as $discovery) {
            $type = $discovery->planet->type;
            $planetTypeCounts[$type] = ($planetTypeCounts[$type] ?? 0) + 1;
        }
        
        $count = $planetTypeCounts[$requiredType] ?? 0;
        return $count >= $requiredCount;
    }
    
    public function getType(): string
    {
        return 'planet_type';
    }
}