<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementDefinition extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'type', 'threshold', 'condition_class', 'is_active'
    ];
    
    protected $casts = [
        'threshold' => 'integer',
        'is_active' => 'boolean'
    ];
    
    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
