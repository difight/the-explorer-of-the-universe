<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Satellite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'current_x', 'current_y', 'current_z',
        'target_x', 'target_y', 'target_z', 'arrival_time', 'status',
        'fuel', 'integrity'
    ];

    protected $casts = [
        'arrival_time' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCurrentSystemAttribute()
    {
        return StarSystem::findOrCreateAt(
            $this->current_x,
            $this->current_y,
            $this->current_z
        );
    }

    public function getTargetSystemAttribute()
    {
        if (!$this->target_x) return null;

        return StarSystem::findOrCreateAt(
            $this->target_x,
            $this->target_y,
            $this->target_z
        );
    }
}
