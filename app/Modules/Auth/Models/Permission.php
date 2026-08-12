<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

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
