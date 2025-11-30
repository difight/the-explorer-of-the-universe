<?php

namespace Database\Factories;

use App\Models\AchievementDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementDefinitionFactory extends Factory
{
    protected $model = AchievementDefinition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->word(),
            'type' => $this->faker->randomElement(['discoveries', 'named_planets', 'satellites_sent', 'energy_spent', 'planet_type', 'special']),
            'threshold' => $this->faker->numberBetween(1, 1000),
            'condition_class' => null,
            'is_active' => true,
        ];
    }

    public function forDiscoveries(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'discoveries',
            'threshold' => $this->faker->numberBetween(1, 100),
        ]);
    }

    public function forNamedPlanets(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'named_planets',
            'threshold' => $this->faker->numberBetween(1, 50),
        ]);
    }

    public function forSatellitesSent(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'satellites_sent',
            'threshold' => $this->faker->numberBetween(1, 200),
        ]);
    }

    public function forEnergySpent(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy_spent',
            'threshold' => $this->faker->numberBetween(100, 5000),
        ]);
    }
    
    public function forPlanetType(string $planetType): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'planet_type',
            'name' => $this->getPlanetTypeName($planetType),
            'threshold' => $this->faker->numberBetween(1, 10),
        ]);
    }
    
    public function forSpecial(string $specialType): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'special',
            'name' => $specialType,
            'threshold' => 1,
        ]);
    }
    
    private function getPlanetTypeName(string $planetType): string
    {
        $names = [
            'gas_giant' => 'Газовый гигант',
            'volcanic' => 'Вулканический мир',
            'oceanic' => 'Океанический исследователь',
            'ice_giant' => 'Ледяной пионер',
            'jungle' => 'Исследователь джунглей',
        ];
        
        return $names[$planetType] ?? 'Исследователь планет';
    }
}