<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discovery extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'planet_id', 'custom_name', 'discovered_at'];

    protected $casts = [
        'discovered_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class);
    }
}
