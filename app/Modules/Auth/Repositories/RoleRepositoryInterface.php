<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\Role;
use App\Modules\Base\Repositories\RepositoryInterface;

interface RoleRepositoryInterface extends RepositoryInterface
{
    /** @param array<string, mixed> $payload */
    public function create(array $payload): Role;

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
    ): Role;
}
