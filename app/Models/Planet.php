<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planet extends Model
{
    use HasFactory;

    protected $fillable = [
        'star_system_id', 'tech_name', 'type', 'has_life',
        'size', 'resource_bonus', 'special_features',
        'orbit_distance', 'temperature'
    ];

    protected $casts = [
        'has_life' => 'boolean',
        'special_features' => 'array',
        'resource_bonus' => 'float',
        'temperature' => 'float'
    ];

    public function starSystem()
    {
        return $this->belongsTo(StarSystem::class);
    }

    public function discoveries()
    {
        return $this->hasMany(Discovery::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $approvedDiscovery = $this->discoveries()
            ->where('status', 'approved')
            ->first();

        return $approvedDiscovery?->custom_name ?? $this->tech_name;
    }

    public function isDiscoveredBy(User $user): bool
    {
        return $this->discoveries()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function getDiscoveryAttribute(): ?Discovery
    {
        return $this->discoveries()->first();
    }
}
