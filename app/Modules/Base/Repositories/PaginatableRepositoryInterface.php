<?php

namespace App\Modules\Base\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaginatableRepositoryInterface
{
    public function paginate(int $perPage = 10, array $relations = [], string $orderBy = 'ASC', array $columns = ['*']): LengthAwarePaginator;

    public function paginateWithQuery(
        array|callable $query,
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator;
}
