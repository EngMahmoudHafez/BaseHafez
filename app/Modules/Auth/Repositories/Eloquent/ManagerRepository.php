<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Model;

class ManagerRepository extends Repository implements ManagerRepositoryInterface
{
    protected Model $model;

    public function __construct(Manager $model)
    {
        parent::__construct($model);
    }

    public function create(array $payload): Manager
    {
        return $this->model->newQuery()->create($payload)->fresh();
    }

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): Manager {
        return $this->model->newQuery()
            ->select($columns)
            ->with($relations)
            ->findOrFail($modelId)
            ->append($appends);
    }
}
