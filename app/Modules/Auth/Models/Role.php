<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laratrust\Models\Role as RoleModel;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $display_name_ar
 * @property string|null $display_name_en
 * @property string|null $description
 */
class Role extends RoleModel
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name_ar',
        'display_name_en',
        'description',
    ];

    public function managers(): MorphToMany
    {
        return $this->morphedByMany(
            Manager::class,
            'model',
            'model_has_roles',
            'role_id',
            'model_id',
        );
    }

    public function managersCount(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->managers()->count();
        });
    }
}
