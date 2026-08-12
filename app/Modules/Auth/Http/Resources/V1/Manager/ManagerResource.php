<?php

namespace App\Modules\Auth\Http\Resources\V1\Manager;

use App\Modules\Auth\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full Manager record for show screens and management APIs. Use
 * {@see ManagerSummaryResource} where only name and photo are needed.
 *
 * @mixin Manager
 */
class ManagerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'status' => $this->status->value,
            'profile_image_url' => $this->image_url,
            'country' => $this->whenLoaded('country', fn (): array => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
            ]),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
