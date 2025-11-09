<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\AchievementDefinition;
use App\Models\User;
use App\Repositories\AchievementRepository;
use App\Achievements\Conditions\PlanetTypeCondition;
use App\Achievements\Conditions\SpecialCondition;

class AchievementService
{
    private AchievementRepository $achievementRepository;
    
    public function __construct(AchievementRepository $achievementRepository)
    {
        $this->achievementRepository = $achievementRepository;
    }
    
    public function checkAllAchievements(User $user): void
    {
        $definitions = $this->achievementRepository->getActiveDefinitions();
        
        foreach ($definitions as $definition) {
            $this->checkAchievement($user, $definition);
        }
    }
    
    public function checkAchievement(User $user, AchievementDefinition $definition): void
    {
        // Проверяем, есть ли уже такое достижение у пользователя
        if ($this->achievementRepository->achievementExists($user, $definition->id)) {
            return;
        }
        
        // Проверяем условия для получения ачивки
        $conditionChecker = $this->getConditionChecker($definition->type);
        if ($conditionChecker) {
            $data = [
                'planet_type' => $definition->type === 'planet_type' ? substr($definition->name, 0, strpos($definition->name, ' ')) : null,
                'count' => $definition->threshold,
                'condition' => $definition->type === 'special' ? $definition->name : null,
            ];
            
            if ($conditionChecker->check($user, $data)) {
                $this->grantAchievement($user, $definition, $data);
            }
        }
    }
    
    public function checkPlanetTypeAchievements(User $user): void
    {
        $definitions = $this->achievementRepository->getDefinitionsByType('planet_type');
        
        foreach ($definitions as $definition) {
            $this->checkAchievement($user, $definition);
        }
    }
    
    public function checkSpecialAchievements(User $user, string $condition, array $data = []): void
    {
        $definitions = $this->achievementRepository->getDefinitionsByType('special');
        
        foreach ($definitions as $definition) {
            if ($definition->name === $condition) {
                $this->checkAchievement($user, $definition);
            }
        }
    }
    
    private function getConditionChecker(string $type): ?object
    {
        switch ($type) {
            case 'planet_type':
                return new PlanetTypeCondition();
            case 'special':
                return new SpecialCondition();
            default:
                return null;
        }
    }
    
    private function grantAchievement(User $user, AchievementDefinition $definition, array $metadata = []): void
    {
        // Проверяем, нет ли уже такого достижения
        if ($this->achievementRepository->achievementExists($user, $definition->id)) {
            return;
        }
        
        $this->achievementRepository->createAchievement($user, $definition, $metadata);
    }
}
