<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'name', 'icon', 'achieved_at', 'metadata', 'threshold', 'description'
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'metadata' => 'array',
        'threshold' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function definition()
    {
        return $this->belongsTo(AchievementDefinition::class);
    }
}
