<?php

namespace App\Modules\Base\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ReadableRepositoryInterface
{
    public function getAll(array $columns = ['*'], array $relations = []): Collection;

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): Model;

    public function get(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): array|Collection;

    public function getWithQuery(
        array|callable $query,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): array|Collection;

    public function first(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): Builder|Model|null;

    public function getFirst(): ?Model;

    public function query(): Builder;
}
