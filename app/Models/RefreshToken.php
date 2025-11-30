<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use Albet\SanctumRefresh\Factories\TokenConfig;
use Albet\SanctumRefresh\Factories\Token;

class RefreshToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'refresh_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token_id',
        'token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the personal access token that owns the refresh token.
     */
    public function personalAccessToken()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'token_id');
    }

    /**
     * Scope a query to check refresh token validity.
     *
     * @param  Builder  $query
     * @param  string  $token
     * @return Builder
     */
    public function scopeCheck(Builder $query, string $token)
    {
        return $query->where('expires_at', '>=', now())
            ->where('token', hash('sha256', $token));
    }

    /**
     * Refresh the token
     *
     * @param string $refreshToken
     * @param string $tokenName
     * @param TokenConfig $tokenConfig
     * @return Token|false
     */
    public static function refreshToken(
        string $refreshToken,
        string $tokenName = 'web',
        TokenConfig $tokenConfig = new TokenConfig()
    ) {
        // Parse the refresh token
        $tokenParts = explode('|', $refreshToken, 2);

        if (count($tokenParts) !== 2) {
            return false;
        }

        [$id, $token] = $tokenParts;
        $id = (int) $id;

        if ($id === 0) {
            return false;
        }

        // Find token from given id and check validity
        $refreshTokenRecord = static::with('personalAccessToken')
            ->check($token)
            ->where('token_id', $id)
            ->first();

        if (!$refreshTokenRecord) {
            return false;
        }

        // Regenerate token.
        $newToken = $refreshTokenRecord->personalAccessToken->tokenable
            ->createToken(
                $tokenName,
                $tokenConfig->abilities,
                $tokenConfig->tokenExpireAt
            );

        $plainRefreshToken = Str::random(40);

        $newRefreshToken = static::create([
            'token_id' => $newToken->accessToken->id,
            'token' => hash('sha256', $plainRefreshToken),
            'expires_at' => $tokenConfig->refreshTokenExpireAt,
        ]);

        // Delete current refresh token first, then the personal access token
        $refreshTokenRecord->delete();
        $refreshTokenRecord->personalAccessToken->delete();

        // Return an object with toArray method
        // Создаем анонимный класс с методом toArray
        return new class($newToken, $plainRefreshToken, $newRefreshToken) {
            public $accessToken;
            public $plainTextToken;
            public $plainRefreshToken;
            public $refreshToken;

            public function __construct($newToken, $plainRefreshToken, $newRefreshToken) {
                $this->accessToken = $newToken;
                $this->plainTextToken = $newToken->plainTextToken;
                $this->plainRefreshToken = $plainRefreshToken;
                $this->refreshToken = $newRefreshToken;
            }

            public function toArray() {
                return [
                    'access_token' => $this->plainTextToken,
                    'access_token_expires_at' => $this->accessToken->accessToken->expires_at,
                    'refresh_token' => $this->refreshToken->token_id . '|' . $this->plainRefreshToken,
                    'refresh_token_expires_at' => $this->refreshToken->expires_at,
                ];
            }
        };
    }
}
