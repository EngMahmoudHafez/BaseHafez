<?php

namespace App\Modules\Notifications\Http\Resources\V1;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'type_label' => __("notifications.types.{$this->type}"),
            'data' => $this->data,
            'action_url' => $this->action_url,
            'icon' => $this->icon ?: $this->defaultIcon(),
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function defaultIcon(): string
    {
        return match ($this->type) {
            'system' => 'tabler-settings',
            'promotion' => 'tabler-speakerphone',
            default => 'tabler-bell',
        };
    }
}
