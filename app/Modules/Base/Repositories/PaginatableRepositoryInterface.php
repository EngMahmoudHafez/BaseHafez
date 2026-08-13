<?php

namespace App\Modules\Base\Repositories;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface PaginatableRepositoryInterface
{
    /**
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 10, array $relations = [], string $orderBy = 'ASC', array $columns = ['*']): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>|Closure  $query
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginateWithQuery(
        array|Closure $query,
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator;
}
