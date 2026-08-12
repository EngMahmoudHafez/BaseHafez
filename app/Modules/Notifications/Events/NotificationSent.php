<?php

namespace App\Modules\Notifications\Events;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a notification is delivered, so realtime web clients receive it
 * on the recipient's private channel. The channel name is
 * `notifications.{type}.{id}`, where `{type}` is the recipient model's base class
 * name (e.g. `notifications.User.42`). Authorize the channel in
 * `routes/channels.php` (see docs/notifications.md).
 */
class NotificationSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification,
        public readonly string $channelName,
    ) {}

    /**
     * Build the private channel name for a recipient.
     */
    public static function channelFor(Model $notifiable): string
    {
        return 'notifications.' . class_basename($notifiable) . '.' . $notifiable->getKey();
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->channelName)];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title_ar' => $this->notification->title_ar,
            'title_en' => $this->notification->title_en,
            'body_ar' => $this->notification->body_ar,
            'body_en' => $this->notification->body_en,
            'data' => $this->notification->data,
            'action_url' => $this->notification->action_url,
            'icon' => $this->notification->icon,
            'is_read' => $this->notification->is_read,
            'created_at' => $this->notification->created_at?->toISOString(),
        ];
    }
}
