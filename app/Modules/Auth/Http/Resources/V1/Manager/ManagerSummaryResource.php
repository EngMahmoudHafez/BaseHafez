<?php

namespace App\Modules\Auth\Http\Resources\V1\Manager;

use App\Modules\Auth\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact Manager representation (name + photo) for lists, embeds, and author
 * badges. Pair with {@see ManagerResource} for the full record.
 *
 * @mixin Manager
 */
class ManagerSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'profile_image_url' => $this->image_url,
        ];
    }
}
