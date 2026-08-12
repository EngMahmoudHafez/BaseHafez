<?php

namespace App\Modules\Notifications\Notifications\Channels;

use App\Modules\Notifications\DTOs\FcmMessage;
use App\Modules\Notifications\Services\Fcm\FcmClient;
use Illuminate\Notifications\Notification;

/**
 * Custom Laravel notification channel. A notification opts in by listing
 * `FcmChannel::class` in its `via()` method and exposing a `toFcm($notifiable)`
 * method that returns an {@see FcmMessage}. Recipient tokens are resolved from the
 * notifiable's `routeNotificationForFcm()` hook (see the HasDeviceTokens trait).
 */
class FcmChannel
{
    public function __construct(
        private readonly FcmClient $client,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        if (! $message instanceof FcmMessage) {
            return;
        }

        $tokens = $this->tokensFor($notifiable, $notification);

        if ($tokens === []) {
            return;
        }

        $this->client->send($message, $tokens);
    }

    /**
     * @return array<int, string>
     */
    private function tokensFor(object $notifiable, Notification $notification): array
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $tokens = $notifiable->routeNotificationFor('fcm', $notification);
        } elseif (method_exists($notifiable, 'deviceTokens')) {
            $tokens = $notifiable->deviceTokens()->pluck('token');
        } else {
            return [];
        }

        return collect($tokens)
            ->filter(static fn (mixed $token): bool => is_string($token) && $token !== '')
            ->unique()
            ->values()
            ->all();
    }
}
