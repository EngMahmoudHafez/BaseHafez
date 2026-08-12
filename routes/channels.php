<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Authorize the private notification channel used by NotificationSent
| (`notifications.{type}.{id}`, where {type} is the recipient model's base
| class name). A subscriber may only listen on their own channel.
|
| Registering a channel resolves the default broadcaster; the base ships
| without a realtime driver package (e.g. Pusher), so guard the call so a
| missing driver never breaks application boot — realtime stays inert until a
| broadcaster is configured, but the authorization rule is otherwise active.
|
*/

try {
    Broadcast::channel('notifications.{type}.{id}', function ($user, string $type, string $id): bool {
        return class_basename($user) === $type
            && (string) $user->getAuthIdentifier() === $id;
    });
} catch (Throwable $exception) {
    report($exception);
}
