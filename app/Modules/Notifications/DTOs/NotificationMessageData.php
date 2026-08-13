<?php

namespace App\Modules\Notifications\DTOs;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Model;

readonly class NotificationMessageData
{
    /** @param array<string, mixed>|null $data */
    public function __construct(
        public string $titleAr,
        public string $titleEn,
        public string $bodyAr,
        public string $bodyEn,
        public string $type = Notification::TYPE_GENERAL,
        public ?array $data = null,
        public ?string $actionUrl = null,
        public ?string $icon = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            titleAr: $data['title_ar'],
            titleEn: ($data['title_en'] ?? null) ?: $data['title_ar'],
            bodyAr: $data['body_ar'],
            bodyEn: ($data['body_en'] ?? null) ?: $data['body_ar'],
            type: $data['type'] ?? Notification::TYPE_GENERAL,
            data: $data['data'] ?? null,
            actionUrl: $data['action_url'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function forNotifiable(Model $notifiable): array
    {
        return [
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->getKey(),
            'title_ar' => $this->titleAr,
            'title_en' => $this->titleEn,
            'body_ar' => $this->bodyAr,
            'body_en' => $this->bodyEn,
            'type' => $this->type,
            'data' => $this->data,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
            'sent_at' => now(),
        ];
    }
}
