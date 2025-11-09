<?php

namespace Database\Factories;

use App\Models\StarSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

class StarSystemFactory extends Factory
{
    protected $model = StarSystem::class;

    public function definition(): array
    {
        return [
            'coord_x' => $this->faker->numberBetween(-1000, 1000),
            'coord_y' => $this->faker->numberBetween(-1000, 1000),
            'coord_z' => $this->faker->numberBetween(-1000, 1000),
            'name' => 'Sector-' . $this->faker->numberBetween(1, 999) . '-' . $this->faker->numberBetween(1, 999) . '-' . $this->faker->numberBetween(1, 999),
            'star_type' => $this->faker->randomElement(['M', 'K', 'G', 'F', 'A', 'B']),
            'star_mass' => $this->faker->randomFloat(2, 0.08, 16.0),
            'is_generated' => $this->faker->boolean(80), // 80% шанс, что система сгенерирована
            'is_start_system' => false,
        ];
    }

    public function startSystem(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_start_system' => true,
            'name' => 'Sol',
            'star_type' => 'G',
            'star_mass' => 1.0,
        ]);
    }

    public function notGenerated(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_generated' => false,
        ]);
    }

    public function generated(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_generated' => true,
        ]);
    }
}