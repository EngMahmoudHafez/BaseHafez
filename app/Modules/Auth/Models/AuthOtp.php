<?php

namespace App\Modules\Auth\Models;

use App\Modules\Auth\database\factories\AuthOtpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $country_id
 * @property string $token
 * @property string $identifier
 * @property string $code_hash
 * @property string $channel
 * @property string $purpose
 * @property Carbon $expires_at
 * @property Carbon|null $verified_at
 * @property int $attempts
 * @property Carbon|null $created_at
 * @property-read User $user
 * @property-read Country|null $country
 */
class AuthOtp extends Model
{
    use HasFactory;

    protected $table = 'auth_otps';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'country_id',
        'token',
        'identifier',
        'code_hash',
        'channel',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function newFactory(): AuthOtpFactory
    {
        return AuthOtpFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
