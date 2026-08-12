<?php

namespace App\Modules\Auth\Http\Resources\V1\User;

use App\Modules\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact User representation for lists and for embedding inside other
 * resources (e.g. a notification's author). Pair with {@see UserResource}
 * for the full record on show screens.
 *
 * @mixin User
 */
class UserSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone ?: $this->whatsapp,
            'profile_image_url' => $this->image_url,
        ];
    }
}
