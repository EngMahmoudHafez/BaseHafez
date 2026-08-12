<?php

namespace App\Modules\Auth\Http\Resources\V1\User;

use App\Modules\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone ?: $this->whatsapp,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'country_id' => $this->country_id,
            'country_code' => $this->country?->dial_code,
            'status' => $this->status->value,
            'avatar' => $this->image_url,
            'profile_image_url' => $this->image_url,
            'last_login_at' => $this->last_login_at?->toISOString(),
        ];
    }
}
