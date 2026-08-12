<?php

namespace App\Modules\Base\Concerns;

use App\Modules\Notifications\Models\DeviceToken;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Give any notifiable Eloquent model a registry of push device tokens. Add the
 * trait alongside Laravel's `Notifiable` trait and nothing else is required:
 *
 *     class User extends Authenticatable
 *     {
 *         use Notifiable, HasDeviceTokens;
 *     }
 *
 *     $user->registerDeviceToken($fcmToken, DeviceToken::PLATFORM_ANDROID);
 *     $user->forgetDeviceToken($fcmToken);   // e.g. on logout
 *     $user->deviceTokens;                    // MorphMany<DeviceToken>
 *
 * `routeNotificationForFcm()` lets the FCM notification channel discover the
 * recipient's tokens automatically, so `$user->notify(...)` just works.
 */
trait HasDeviceTokens
{
    /**
     * @return MorphMany<DeviceToken, $this>
     */
    public function deviceTokens(): MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'tokenable');
    }

    /**
     * Register (or refresh) a device token for this recipient. A token is globally
     * unique, so re-registering one that belongs to another recipient reassigns it
     * to this recipient instead of failing on the unique constraint.
     */
    public function registerDeviceToken(string $token, ?string $platform = null): DeviceToken
    {
        $deviceToken = DeviceToken::firstOrNew(['token' => $token]);
        $deviceToken->fill([
            'platform' => $platform,
            'last_used_at' => now(),
        ]);
        $deviceToken->tokenable()->associate($this);
        $deviceToken->save();

        return $deviceToken;
    }

    public function forgetDeviceToken(string $token): void
    {
        $this->deviceTokens()->where('token', $token)->delete();
    }

    /**
     * Token discovery hook for the FCM notification channel.
     *
     * @return Collection<int, string>
     */
    public function routeNotificationForFcm(Notification $notification): Collection
    {
        return $this->deviceTokens()->pluck('token');
    }
}
