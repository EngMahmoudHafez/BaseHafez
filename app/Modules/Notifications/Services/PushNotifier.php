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
 * The database record is user-scoped (it reuses the existing `notifications` table,
 * keyed by `user_id`), so `$notifiable` must be a User or share the `users` table.
 * Device push and broadcast are polymorphic and work for any notifiable that uses
 * the HasDeviceTokens trait.
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
        // The notifications table is user-scoped (user_id FK to users). Filing a
        // record for a model on another table would collide with a same-id User or
        // violate the FK — fail fast instead of silently mis-filing it.
        if ($notifiable->getTable() !== 'users') {
            throw new \InvalidArgumentException(
                'PushNotifier persists to the user-scoped notifications table; $notifiable must live on the users table, got '
                . $notifiable::class . ' (' . $notifiable->getTable() . ').',
            );
        }

        return Notification::create([
            'user_id' => $notifiable->getKey(),
            'title_ar' => $title,
            'title_en' => $title,
            'body_ar' => $body,
            'body_en' => $body,
            'type' => $type,
            'data' => $data === [] ? null : $data,
            'sent_at' => now(),
        ]);
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
