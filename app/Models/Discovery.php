<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'planet_id', 'custom_name', 'status', 
        'discovered_at', 'rejection_reason', 'moderated_at', 'moderated_by'
    ];

    protected $casts = [
        'discovered_at' => 'datetime',
        'moderated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeRenamed(): bool
    {
        return $this->isPending() && empty($this->custom_name);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeNeedsModeration($query)
    {
        return $query->where('status', 'pending')->whereNotNull('custom_name');
    }
}