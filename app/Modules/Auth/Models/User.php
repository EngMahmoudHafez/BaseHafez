<?php

namespace App\Modules\Auth\Models;

use App\Modules\Auth\database\factories\UserFactory;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Base\Concerns\HasDeviceTokens;
use App\Modules\Base\Concerns\HasImages;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @property int $id
 * @property int|null $country_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string|null $email
 * @property string|null $password
 * @property string|null $avatar
 * @property UserStatus $status
 * @property int $token_version
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $image_url
 * @property-read Country|null $country
 * @property-read Collection<int, AuthOtp> $authOtps
 */
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasDeviceTokens, HasFactory, HasImages, Notifiable;

    protected $fillable = [
        'country_id',
        'name',
        'phone',
        'whatsapp',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Ensure a brand-new (unsaved) user already carries the token generation the
     * database defaults to, so the `tv` claim minted at token issuance matches the
     * persisted `token_version`.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'token_version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'token_version' => 'integer',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** @return array<string, mixed> */
    public function getJWTCustomClaims(): array
    {
        return ['tv' => $this->tokenVersion()];
    }

    /**
     * The current token generation. Every JWT carries it as the `tv` claim;
     * bumping it (on password change/reset) revokes all previously issued tokens.
     */
    public function tokenVersion(): int
    {
        return (int) $this->getAttribute('token_version');
    }

    public function isActiveAccount(): bool
    {
        return $this->getAttribute('status') === UserStatus::Active;
    }

    public function token(): string
    {
        return JWTAuth::fromUser($this);
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /** @return HasMany<AuthOtp, $this> */
    public function authOtps(): HasMany
    {
        return $this->hasMany(AuthOtp::class, 'user_id');
    }
}
