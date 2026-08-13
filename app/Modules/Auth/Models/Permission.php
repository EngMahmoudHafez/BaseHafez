<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Laratrust\Models\Permission as PermissionModel;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Manager> $managers
 */
class Permission extends PermissionModel
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /** @return MorphToMany<Manager, $this> */
    public function managers(): MorphToMany
    {
        return $this->morphedByMany(
            Manager::class,
            'model',
            'model_has_permissions',
            'permission_id',
            'model_id',
        );
    }
}
