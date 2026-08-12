<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\Manager;
use App\Modules\Base\Repositories\RepositoryInterface;

interface ManagerRepositoryInterface extends RepositoryInterface
{
    public function create(array $payload): Manager;

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): Manager;
}
