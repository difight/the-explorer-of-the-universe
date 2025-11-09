<?php

namespace App\Achievements\Conditions;

use App\Models\User;

abstract class BaseAchievementCondition implements AchievementCondition
{
    public function check(User $user, array $data = []): bool
    {
        // Базовая реализация - всегда возвращает false
        // Должна быть переопределена в подклассах
        return false;
    }
    
    public function getType(): string
    {
        // Возвращает тип ачивки по умолчанию - имя класса в snake_case
        $className = class_basename($this);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', substr($className, 0, -9))); // Убираем "Condition"
    }
}