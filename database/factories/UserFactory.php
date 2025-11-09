<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'energy' => 100,
            'max_energy' => 100,
            'last_energy_regen' => now(),
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    public function withLowEnergy(): self
    {
        return $this->state(fn (array $attributes) => [
            'energy' => 10,
            'max_energy' => 100,
        ]);
    }

    public function withHighEnergy(): self
    {
        return $this->state(fn (array $attributes) => [
            'energy' => 150,
            'max_energy' => 150,
        ]);
    }
}
