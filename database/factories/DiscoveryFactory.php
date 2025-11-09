<?php

namespace Database\Factories;

use App\Models\Discovery;
use App\Models\User;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscoveryFactory extends Factory
{
    protected $model = Discovery::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'planet_id' => Planet::factory(),
            'custom_name' => null,
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'discovered_at' => now(),
            'rejection_reason' => null,
            'moderated_at' => null,
            'moderated_by' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'moderated_at' => now(),
            'moderated_by' => User::factory()->admin(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
            'moderated_at' => now(),
            'moderated_by' => User::factory()->admin(),
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'custom_name' => $this->faker->optional(0.7)->word . ' ' . $this->faker->optional(0.7)->word,
        ]);
    }

    public function withCustomName(): self
    {
        return $this->state(fn (array $attributes) => [
            'custom_name' => $this->faker->word . ' ' . $this->faker->word,
        ]);
    }

    public function withoutCustomName(): self
    {
        return $this->state(fn (array $attributes) => [
            'custom_name' => null,
        ]);
    }
}