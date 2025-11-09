<?php

namespace Database\Factories;

use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanetFactory extends Factory
{
    protected $model = Planet::class;

    public function definition(): array
    {
        return [
            'star_system_id' => StarSystem::factory(),
            'tech_name' => $this->faker->word . '-' . $this->faker->randomNumber(3),
            'type' => $this->faker->randomElement([
                'barren', 'desert', 'oceanic', 'tundra', 'gas_giant', 
                'volcanic', 'ice_giant', 'jungle', 'toxic', 'terrestrial',
                'crystal', 'swamp'
            ]),
            'has_life' => $this->faker->boolean(10), // 10% шанс жизни
            'size' => $this->faker->numberBetween(1000, 150000),
            'resource_bonus' => $this->faker->randomFloat(2, 0.5, 3.0),
            'special_features' => $this->faker->randomElements([
                'rings', 'strong_magnetic_field', 'volcanic_activity', 
                'cryo_volcanoes', 'methane_lakes', 'crystal_formations',
                'ancient_ruins', 'quantum_anomaly', 'floating_islands',
                'subsurface_ocean', 'aurora_borealis', 'acid_rains',
                'bioluminescent_flora', 'geothermal_vents', 'sandstorms'
            ], $this->faker->numberBetween(0, 3)),
            'orbit_distance' => $this->faker->numberBetween(1, 10),
            'temperature' => $this->faker->randomFloat(1, -250, 500),
            'color' => $this->faker->randomElement([
                'gray', 'yellow', 'blue', 'white', 'orange', 'red', 
                'light-blue', 'green', 'purple', 'brown', 'pink', 'dark-green'
            ]),
        ];
    }

    public function barren(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'barren',
            'has_life' => false,
            'size' => $this->faker->numberBetween(1000, 8000),
            'temperature' => $this->faker->randomFloat(1, -200, 200),
            'color' => 'gray',
        ]);
    }

    public function desert(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'desert',
            'has_life' => $this->faker->boolean(1),
            'size' => $this->faker->numberBetween(4000, 12000),
            'temperature' => $this->faker->randomFloat(1, -50, 80),
            'color' => 'yellow',
        ]);
    }

    public function oceanic(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'oceanic',
            'has_life' => $this->faker->boolean(5),
            'size' => $this->faker->numberBetween(8000, 15000),
            'temperature' => $this->faker->randomFloat(1, -20, 40),
            'color' => 'blue',
        ]);
    }

    public function withLife(): self
    {
        return $this->state(fn (array $attributes) => [
            'has_life' => true,
        ]);
    }

    public function withoutLife(): self
    {
        return $this->state(fn (array $attributes) => [
            'has_life' => false,
        ]);
    }
}