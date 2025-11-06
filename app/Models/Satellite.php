<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satellite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'current_x', 'current_y', 'current_z',
        'target_x', 'target_y', 'target_z', 'arrival_time', 'status',
        'energy', 'integrity', 'malfunctions'
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'malfunctions' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCurrentSystemAttribute(): StarSystem
    {
        return StarSystem::findOrCreateAt(
            $this->current_x,
            $this->current_y,
            $this->current_z
        );
    }

    public function getTargetSystemAttribute(): ?StarSystem
    {
        if (!$this->target_x) {
            return null;
        }

        return StarSystem::findOrCreateAt(
            $this->target_x,
            $this->target_y,
            $this->target_z
        );
    }

    public function isTraveling(): bool
    {
        return $this->status === 'traveling';
    }

    public function hasArrived(): bool
    {
        return $this->isTraveling() && $this->arrival_time?->isPast();
    }

    public function getTravelProgressAttribute(): float
    {
        if (!$this->isTraveling() || !$this->arrival_time) {
            return 0;
        }

        $totalTime = $this->arrival_time->diffInSeconds($this->created_at);
        $elapsedTime = now()->diffInSeconds($this->created_at);

        return min(100, ($elapsedTime / $totalTime) * 100);
    }
}
