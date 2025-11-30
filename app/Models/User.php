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

    /**
     * Get the refresh tokens for the user.
     */
    public function refreshTokens()
    {
        // Получаем таблицу refresh_tokens
        return $this->hasMany(\Laravel\Sanctum\PersonalAccessToken::class, 'tokenable_id')
                    ->join('refresh_tokens', 'personal_access_tokens.id', '=', 'refresh_tokens.token_id');
    }

    /**
     * Check if user has a valid refresh token.
     *
     * @param string $refreshToken
     * @return bool
     */
    public static function hasValidRefreshToken($refreshToken)
    {
        // Разделяем токен на id и токен
        $parts = explode('|', $refreshToken, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$id, $token] = $parts;

        // Ищем refresh токен в таблице refresh_tokens по token_id и хэшированному токену
        $refreshTokenRecord = RefreshToken::where('token_id', $id)
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        return !is_null($refreshTokenRecord);
    }

    /**
     * Clear all refresh tokens for the user.
     */
    public function clearRefreshTokens($exceptTokenId = null)
    {
        // Получаем все токены пользователя
        $tokens = $this->tokens()->get();

        // Удаляем связанные refresh токены
        foreach ($tokens as $token) {
            // Пропускаем токен, который не нужно удалять
            if ($exceptTokenId && $token->id == $exceptTokenId) {
                continue;
            }
            RefreshToken::where('token_id', $token->id)->delete();
        }
    }

    /**
     * Get user by refresh token if not expired.
     *
     * @param string $refreshToken
     * @return User|null
     */
    public static function byRefreshToken($refreshToken)
    {
        // Разделяем токен на id и токен
        $parts = explode('|', $refreshToken, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$id, $token] = $parts;

        // Ищем refresh токен в таблице refresh_tokens по token_id и хэшированному токену
        $refreshTokenRecord = RefreshToken::where('token_id', $id)
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$refreshTokenRecord) {
            return null;
        }

        // Получаем токен из таблицы personal_access_tokens
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::find($refreshTokenRecord->token_id);

        if (!$personalAccessToken) {
            return null;
        }

        // Возвращаем пользователя
        return $personalAccessToken->tokenable;
    }
}
