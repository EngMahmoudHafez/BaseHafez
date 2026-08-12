<?php

namespace App\Modules\Auth\Rules;

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Field-shaped authorization: a manager may only assign a role whose authority
 * rank is at or below their own. Super admins may assign any role. This blocks
 * a lower-privileged manager from granting `super_admin`/`admin` (or any role
 * above their own rank) to themselves or others.
 */
class AssignableRole implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $actor = auth('manager')->user();

        if (! $actor instanceof Manager) {
            $fail('auth.role_not_assignable')->translate();

            return;
        }

        if ($actor->isSuperAdmin()) {
            return;
        }

        $role = Role::query()->whereKey($value)->first();

        // A missing role is left to the `exists:roles,id` rule to report.
        if ($role instanceof Role && RoleName::rankFor($role->name) > $actor->highestRank()) {
            $fail('auth.role_not_assignable')->translate();
        }
    }
}
