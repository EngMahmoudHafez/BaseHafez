<?php

namespace App\Modules\Notifications\Services;

use App\Modules\Notifications\Events\NotificationSent;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Notifications\PushNotification;
use Illuminate\Database\Eloquent\Model;

/**
 * Single entry point for delivering a notification everywhere at once.
 *
 * `send()` performs three steps: it persists a database {@see Notification} record,
 * pushes the message to the recipient's registered devices over FCM, and broadcasts
 * it on the recipient's private channel for realtime web clients. FCM and broadcast
 * are best-effort and env-gated, so the database record is always written.
 *
 *     app(PushNotifier::class)->send($user, 'Welcome', 'Thanks for joining', ['order_id' => 42]);
 *
 * The database record is polymorphic (`notifiable_type` + `notifiable_id`), so any
 * Eloquent model can receive notifications — a User, a Manager, or a project's own
 * Vendor/Driver/Customer — without changing this module. Device push and broadcast
 * additionally require the recipient to use the HasDeviceTokens trait.
 */
class PushNotifier
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function send(
        Model $notifiable,
        string $title,
        string $body,
        array $data = [],
        string $type = Notification::TYPE_GENERAL,
    ): Notification {
        $notification = $this->storeNotification($notifiable, $title, $body, $data, $type);

        $this->deliverExisting($notifiable, $notification);

        return $notification;
    }

    /**
     * Push and broadcast an already-persisted notification (used when the caller
     * created the DB record itself, e.g. the bilingual dashboard broadcast) so it
     * actually reaches devices and realtime clients rather than only the database.
     */
    public function deliverExisting(Model $notifiable, Notification $notification): void
    {
        $this->pushToDevices(
            $notifiable,
            (string) ($notification->getAttribute('title_en') ?: $notification->getAttribute('title_ar')),
            (string) ($notification->getAttribute('body_en') ?: $notification->getAttribute('body_ar')),
            (array) ($notification->getAttribute('data') ?? []),
        );

        event(new NotificationSent($notification, NotificationSent::channelFor($notifiable)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeNotification(Model $notifiable, string $title, string $body, array $data, string $type): Notification
    {
        $notification = new Notification([
            'title_ar' => $title,
            'title_en' => $title,
            'body_ar' => $body,
            'body_en' => $body,
            'type' => $type,
            'data' => $data === [] ? null : $data,
            'sent_at' => now(),
        ]);

        $notification->notifiable()->associate($notifiable);
        $notification->save();

        return $notification;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pushToDevices(Model $notifiable, string $title, string $body, array $data): void
    {
        if (! method_exists($notifiable, 'notify') || ! method_exists($notifiable, 'deviceTokens')) {
            return;
        }

        if ($notifiable->deviceTokens()->doesntExist()) {
            return;
        }

        $notifiable->notify(new PushNotification($title, $body, $data));
    }
}
