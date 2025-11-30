<?php

use App\Models\Planet;
use App\Models\StarSystem;
use App\Models\User;
use App\Models\Discovery;

test('planet has correct attributes', function () {
    $planet = Planet::factory()->create([
        'tech_name' => 'Test Planet',
        'type' => 'terrestrial',
        'has_life' => true,
        'size' => 10000,
        'resource_bonus' => 1.5,
        'special_features' => ['rings', 'volcanic_activity'],
        'orbit_distance' => 3,
        'temperature' => 25.5,
        'color' => 'green',
    ]);

    expect($planet->tech_name)->toBe('Test Planet');
    expect($planet->type)->toBe('terrestrial');
    expect($planet->has_life)->toBeTrue();
    expect($planet->size)->toBe(10000);
    expect($planet->resource_bonus)->toBe(1.5);
    expect($planet->special_features)->toBe(['rings', 'volcanic_activity']);
    expect($planet->orbit_distance)->toBe(3);
    expect($planet->temperature)->toBe(25.5);
    expect($planet->color)->toBe('green');
});

test('planet belongs to star system', function () {
    $starSystem = StarSystem::factory()->create();
    $planet = Planet::factory()->create(['star_system_id' => $starSystem->id]);

    expect($planet->starSystem)->toBeInstanceOf(StarSystem::class);
    expect($planet->starSystem->id)->toBe($starSystem->id);
});

test('planet has discoveries', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();
    $discovery = Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
    ]);

    expect($planet->discoveries)->toHaveCount(1);
    expect($planet->discoveries->first())->toBeInstanceOf(Discovery::class);
    expect($planet->discoveries->first()->id)->toBe($discovery->id);
});

test('planet display name returns custom name when approved discovery exists', function () {
    $planet = Planet::factory()->create(['tech_name' => 'Tech Planet']);
    $user = User::factory()->create();
    Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
        'custom_name' => 'Custom Planet Name',
        'status' => 'approved',
    ]);

    expect($planet->display_name)->toBe('Custom Planet Name');
});

test('planet display name returns tech name when no approved discovery exists', function () {
    $planet = Planet::factory()->create(['tech_name' => 'Tech Planet']);
    $user = User::factory()->create();
    Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
        'custom_name' => 'Custom Planet Name',
        'status' => 'pending',
    ]);

    expect($planet->display_name)->toBe('Tech Planet');
});

test('planet can be named by user when user has pending discovery without name', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();
    Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
        'custom_name' => null,
        'status' => 'pending',
    ]);

    expect($planet->getCanBeNamedByUser($user))->toBeTrue();
});

test('planet cannot be named by user when user has no discovery', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();

    expect($planet->getCanBeNamedByUser($user))->toBeFalse();
});

test('planet cannot be named by user when discovery already has name', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();
    Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
        'custom_name' => 'Existing Name',
        'status' => 'pending',
    ]);

    expect($planet->getCanBeNamedByUser($user))->toBeFalse();
});

test('planet is discovered by user when discovery exists', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();
    Discovery::factory()->create([
        'planet_id' => $planet->id,
        'user_id' => $user->id,
    ]);

    expect($planet->isDiscoveredBy($user))->toBeTrue();
});

test('planet is not discovered by user when no discovery exists', function () {
    $planet = Planet::factory()->create();
    $user = User::factory()->create();

    expect($planet->isDiscoveredBy($user))->toBeFalse();
});