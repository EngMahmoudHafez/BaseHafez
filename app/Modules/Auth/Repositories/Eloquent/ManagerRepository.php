<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;

/**
 * @extends Repository<Manager>
 */
class ManagerRepository extends Repository implements ManagerRepositoryInterface
{
    public function __construct(Manager $model)
    {
        parent::__construct($model);
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload): Manager
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
    ): Manager {
        return parent::getById($modelId, $columns, $relations, $appends);
    }
}
