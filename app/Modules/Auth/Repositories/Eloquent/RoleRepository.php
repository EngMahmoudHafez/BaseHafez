<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Repositories\RoleRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;

/**
 * @extends Repository<Role>
 */
class RoleRepository extends Repository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload): Role
    {
        return parent::create($payload);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $appends
     */
    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): Role {
        return parent::getById($modelId, $columns, $relations, $appends);
    }
}
