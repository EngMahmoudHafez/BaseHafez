# Push notifications & broadcasting

One call delivers a notification three ways at once: a database record, a Firebase
Cloud Messaging (FCM) push to the recipient's devices, and a realtime broadcast on
the recipient's private channel. Everything is env-gated, so local and testing
environments work with no credentials.

## The one call

```php
use App\Modules\Notifications\Services\PushNotifier;

app(PushNotifier::class)->send(
    $user,                       // any notifiable model (see "Enabling a model")
    'Welcome',                   // title
    'Thanks for joining',        // body
    ['order_id' => 42],          // optional data payload
    // Notification::TYPE_GENERAL // optional type (general | system | promotion)
);
```

`send()` returns the persisted `App\Modules\Notifications\Models\Notification`. It:

1. writes the database record (reusing the existing `notifications` table),
2. pushes to the recipient's registered devices over FCM (skipped when the model
   has no device tokens or FCM is unconfigured),
3. broadcasts a `NotificationSent` event on the private channel
   `notifications.{type}.{id}` (e.g. `notifications.User.42`).

The database record is **user-scoped** (`notifications.user_id`), so `$notifiable`
must be a `User` or share the `users` table. FCM push and broadcast are polymorphic
and work for any model that uses the `HasDeviceTokens` trait.

## Enabling a model

`HasDeviceTokens` is **already** applied (next to Laravel's `Notifiable` trait) on the
shipped `User` and `Manager` models. Add it the same way on any additional model that
should receive push notifications:

```php
use App\Modules\Base\Concerns\HasDeviceTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasDeviceTokens;
}
```

## Registering a device token

Register the token your mobile/web client obtains from Firebase (e.g. from a
`POST /device-tokens` endpoint you own), and forget it on logout:

```php
$user->registerDeviceToken($fcmToken, 'android'); // 'android' | 'ios' | 'web' | null
$user->forgetDeviceToken($fcmToken);
```

A token is globally unique; re-registering one that belonged to another account
reassigns it to the current user automatically.

## Environment variables

FCM push stays off until both values are set. Point `FCM_CREDENTIALS` at a Google
service-account JSON file (never commit it):

```dotenv
FCM_PROJECT_ID=your-firebase-project-id
FCM_CREDENTIALS=/absolute/path/to/service-account.json
```

Broadcasting defaults to the safe `log` driver. Leave the rest unset for local work:

```dotenv
BROADCAST_CONNECTION=log        # switch to "reverb" or "pusher" in production
QUEUE_CONNECTION=sync           # use a real queue so pushes defer off the request
```

## Switching on Pusher or Reverb

The `pusher` and `reverb` connections are already defined in
`config/broadcasting.php`, gated entirely by env. To go live, install the matching
driver and set the connection:

- **Reverb** — `composer require laravel/reverb`, then:

  ```dotenv
  BROADCAST_CONNECTION=reverb
  REVERB_APP_ID=
  REVERB_APP_KEY=
  REVERB_APP_SECRET=
  REVERB_HOST=
  REVERB_PORT=443
  REVERB_SCHEME=https
  ```

- **Pusher** — `composer require pusher/pusher-php-server`, then:

  ```dotenv
  BROADCAST_CONNECTION=pusher
  PUSHER_APP_ID=
  PUSHER_APP_KEY=
  PUSHER_APP_SECRET=
  PUSHER_APP_CLUSTER=mt1
  ```

## Private channel authorization

The base already ships this authorization in `routes/channels.php`, so you do not
need to add it. It allows the authenticated recipient whose base class name and key
match the channel, and is wrapped in a `try/catch` so a missing broadcaster driver
never breaks application boot. Adjust it only if your recipients authenticate through
a non-default broadcasting guard:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('notifications.{type}.{id}', function ($user, string $type, string $id): bool {
    return class_basename($user) === $type && (string) $user->getAuthIdentifier() === $id;
});
```

Client subscription (Laravel Echo):

```js
Echo.private(`notifications.User.${userId}`)
    .listen('.notification.sent', (payload) => { /* payload = broadcastWith() */ });
```

## Adding push to your own notifications

Any Laravel notification can carry a push payload by adding the FCM channel and a
`toFcm()` method (the convention `PushNotification` already follows):

```php
use App\Modules\Notifications\DTOs\FcmMessage;
use App\Modules\Notifications\Notifications\Channels\FcmChannel;

public function via(object $notifiable): array
{
    return [FcmChannel::class];
}

public function toFcm(object $notifiable): FcmMessage
{
    return new FcmMessage('Title', 'Body', ['key' => 'value']);
}
```
