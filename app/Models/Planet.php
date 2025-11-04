<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Planet extends Model
{
    use HasFactory;

    protected $fillable = [
        'star_system_id', 'tech_name', 'type', 'has_life',
        'size', 'resource_bonus', 'special_features'
    ];

    protected $casts = [
        'special_features' => 'array',
        'has_life' => 'boolean'
    ];

    public function starSystem()
    {
        return $this->belongsTo(StarSystem::class);
    }

    public function discoveries()
    {
        return $this->hasMany(Discovery::class);
    }

    public function getDisplayNameAttribute()
    {
        return $this->discoveries->first()?->custom_name ?? $this->tech_name;
    }
}
