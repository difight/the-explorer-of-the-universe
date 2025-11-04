<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StarSystem extends Model
{
    use HasFactory;

    protected $fillable = ['coord_x', 'coord_y', 'coord_z', 'name', 'is_generated'];

    public function planets()
    {
        return $this->hasMany(Planet::class);
    }

    // Найти систему по координатам или создать запись
    public static function findOrCreateAt(int $x, int $y, int $z): self
    {
        return self::firstOrCreate(
            ['coord_x' => $x, 'coord_y' => $y, 'coord_z' => $z],
            [
                'name' => "Sector-{$x}-{$y}-{$z}",
                'is_generated' => false
            ]
        );
    }
}
