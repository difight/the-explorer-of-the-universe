<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->word(),
            'threshold' => $this->faker->numberBetween(1, 1000),
            'type' => $this->faker->randomElement(['discoveries', 'named_planets', 'satellites_sent', 'energy_spent', 'planet_type', 'special']),
            'achieved_at' => now(),
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
}