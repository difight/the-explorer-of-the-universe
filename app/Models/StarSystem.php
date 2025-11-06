<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'coord_x', 'coord_y', 'coord_z', 'name', 'star_type',
        'star_mass', 'is_generated', 'is_start_system'
    ];

    protected $casts = [
        'is_generated' => 'boolean',
        'is_start_system' => 'boolean',
        'star_mass' => 'float'
    ];

    public function planets()
    {
        return $this->hasMany(Planet::class);
    }

    public function discoveries()
    {
        return $this->hasManyThrough(Discovery::class, Planet::class);
    }

    public static function findOrCreateAt(int $x, int $y, int $z): self
    {
        return self::firstOrCreate(
            ['coord_x' => $x, 'coord_y' => $y, 'coord_z' => $z],
            [
                'name' => "Sector-{$x}-{$y}-{$z}",
                'is_generated' => false,
                'is_start_system' => false
            ]
        );
    }

    public function getCoordinatesAttribute(): string
    {
        return "{$this->coord_x},{$this->coord_y},{$this->coord_z}";
    }
}
