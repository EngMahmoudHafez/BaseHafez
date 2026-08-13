<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Repositories\PermissionRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;

/**
 * @extends Repository<Permission>
 */
class PermissionRepository extends Repository implements PermissionRepositoryInterface
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }
}
