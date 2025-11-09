<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SvgPlanetService;
use Illuminate\Support\Facades\Storage;

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
            ->whereNotNull('custom_name')
            ->first();

        if ($approvedDiscovery) {
            return $approvedDiscovery->custom_name;
        }

        // Или техническое название
        return $this->tech_name;
    }

    public function canBeNamedByUser(User $user): bool
    {
        // Проверяем, может ли пользователь назвать эту планету
        $userDiscovery = $this->discoveries()
            ->where('user_id', $user->id)
            ->first();

        return $userDiscovery && $userDiscovery->canBeRenamed();
    }

    public function getUserDiscovery(User $user): ?Discovery
    {
        return $this->discoveries()
            ->where('user_id', $user->id)
            ->first();
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

    public function getPlanetImageUrl(): string
    {
        $filename = 'planets/' . $this->id . '.svg';

        if (Storage::exists($filename)) {
            return Storage::url($filename);
        }

        $svgService = new SvgPlanetService();

        $svgContent = $svgService->generateSvgPlanet([
            'type' => $this->type,
            'temperature' => $this->temperature,
            'orbit_distance' => $this->orbit_distance,
            'special_features' => $this->special_features,
            'has_life' => $this->has_life,
            'size' => $this->size,
        ]);

        Storage::put($filename, $svgContent);
        return Storage::url($filename);
    }
}
