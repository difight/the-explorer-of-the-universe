<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
    public function satellite()
    {
        return $this->hasOne(Satellite::class);
    }

    public function discoveries()
    {
        return $this->hasMany(Discovery::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function getApprovedDiscoveriesAttribute()
    {
        return $this->discoveries()->approved()->get();
    }

    public function getDiscoveredPlanetsWithLifeAttribute()
    {
        return $this->discoveries()
            ->whereHas('planet', function ($query) {
                $query->where('has_life', true);
            })
            ->approved()
            ->get();
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->satellite()->create([
                'name' => 'Explorer-' . $user->id,
                'current_x' => 0,
                'current_y' => 0,
                'current_z' => 0,
            ]);
        });
    }
}
