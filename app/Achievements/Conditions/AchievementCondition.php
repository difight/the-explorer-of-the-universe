<?php

namespace App\Achievements\Conditions;

use App\Models\User;

interface AchievementCondition
{
    public function check(User $user, array $data = []): bool;
    
    public function getType(): string;
}