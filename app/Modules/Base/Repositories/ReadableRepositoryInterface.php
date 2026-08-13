<?php

namespace App\Modules\Base\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ReadableRepositoryInterface
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return Collection<int, Model>
     */
    public function getAll(array $columns = ['*'], array $relations = []): Collection;

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
    ): Model;

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return array<int, Model>|Collection<int, Model>
     */
    public function get(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): array|Collection;

    /**
     * @param  array<string, mixed>|Closure  $query
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return array<int, Model>|Collection<int, Model>
     */
    public function getWithQuery(
        array|Closure $query,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): array|Collection;

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     */
    public function first(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): ?Model;

    public function getFirst(): ?Model;

    /** @return Builder<Model> */
    public function query(): Builder;
}
