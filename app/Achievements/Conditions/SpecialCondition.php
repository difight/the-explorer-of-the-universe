<?php

namespace App\Achievements\Conditions;

use App\Models\User;

class SpecialCondition extends BaseAchievementCondition
{
    public function check(User $user, array $data = []): bool
    {
        $condition = $data['condition'] ?? null;
        
        if (!$condition) {
            return false;
        }
        
        switch ($condition) {
            case 'first_system':
                // Проверяем, является ли это первым открытием
                return $user->discoveries()->count() >= 1;
                
            case 'found_life':
                // Проверяем, нашел ли пользователь планету с жизнью
                return $user->discoveries()
                    ->whereHas('planet', function ($query) {
                        $query->where('has_life', true);
                    })
                    ->exists();
                    
            case 'life_in_10_systems':
                // Проверяем, нашел ли пользователь жизнь в 10 системах
                $systemsWithLife = $user->discoveries()
                    ->whereHas('planet', function ($query) {
                        $query->where('has_life', true);
                    })
                    ->join('planets', 'discoveries.planet_id', '=', 'planets.id')
                    ->join('star_systems', 'planets.star_system_id', '=', 'star_systems.id')
                    ->distinct()
                    ->pluck('star_systems.id');
                    
                return $systemsWithLife->count() >= 10;
                
            case '100_systems':
                // Проверяем, исследовал ли пользователь 100 систем
                $systemsCount = $user->discoveries()
                    ->join('planets', 'discoveries.planet_id', '=', 'planets.id')
                    ->join('star_systems', 'planets.star_system_id', '=', 'star_systems.id')
                    ->distinct()
                    ->count('star_systems.id');
                    
                return $systemsCount >= 100;
                
            default:
                return false;
        }
    }
    
    public function getType(): string
    {
        return 'special';
    }
}