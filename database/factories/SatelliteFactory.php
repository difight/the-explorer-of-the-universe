<?php

namespace Database\Factories;

use App\Models\Satellite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SatelliteFactory extends Factory
{
    protected $model = Satellite::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Explorer-' . $this->faker->numberBetween(1, 9999),
            'current_x' => 0,
            'current_y' => 0,
            'current_z' => 0,
            'target_x' => null,
            'target_y' => null,
            'target_z' => null,
            'arrival_time' => null,
            'status' => 'idle',
            'energy' => $this->faker->numberBetween(50, 100),
            'integrity' => $this->faker->numberBetween(50, 100),
            'malfunctions' => null,
        ];
    }

    public function traveling(): self
    {
        return $this->state(fn (array $attributes) => [
            'target_x' => $this->faker->numberBetween(-100, 100),
            'target_y' => $this->faker->numberBetween(-100, 100),
            'target_z' => $this->faker->numberBetween(-100, 100),
            'arrival_time' => now()->addHours($this->faker->numberBetween(1, 72)),
            'status' => 'traveling',
        ]);
    }

    public function lowEnergy(): self
    {
        return $this->state(fn (array $attributes) => [
            'energy' => $this->faker->numberBetween(0, 20),
        ]);
    }

    public function damaged(): self
    {
        return $this->state(fn (array $attributes) => [
            'integrity' => $this->faker->numberBetween(0, 30),
            'malfunctions' => $this->faker->randomElements([
                'engine', 'sensors', 'communications', 'power', 'navigation'
            ], $this->faker->numberBetween(1, 3)),
        ]);
    }
}