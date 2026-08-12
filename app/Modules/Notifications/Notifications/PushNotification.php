<?php

namespace App\Modules\Notifications\Notifications;

use App\Modules\Notifications\DTOs\FcmMessage;
use App\Modules\Notifications\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Queued Laravel notification delivered through the FCM channel. The `toFcm()`
 * convention lets any notification describe its push payload; dispatch is deferred
 * to the queue so the originating request stays fast.
 */
class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return new FcmMessage($this->title, $this->body, $this->data);
    }
}
