<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Repositories\RoleRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;

class RoleRepository extends Repository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }
}
