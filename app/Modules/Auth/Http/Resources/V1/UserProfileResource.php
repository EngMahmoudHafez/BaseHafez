<?php

namespace App\Modules\Auth\Http\Resources\V1;

use App\Modules\Auth\Http\Resources\V1\User\UserResource;
use App\Support\PersonalName;
use Illuminate\Http\Request;

/**
 * The authenticated user's own profile: the canonical {@see UserResource}
 * record plus the split first/last name the profile screen edits.
 */
class UserProfileResource extends UserResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        [$firstName, $lastName] = PersonalName::split($this->name);

        return [
            ...parent::toArray($request),
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }
}
