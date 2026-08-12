<?php

namespace App\Modules\Base\Repositories\Eloquent;

use App\Modules\Base\Concerns\FileTrait;
use App\Modules\Base\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
    use FileTrait;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query()->with($relations)->get($columns);
    }

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): Model {
        return $this->query()
            ->select($columns)
            ->with($relations)
            ->findOrFail($modelId)
            ->append($appends);
    }

    public function get(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): Collection {
        return $this->query()
            ->select($columns)
            ->with($relations)
            ->where($byColumn, $value)
            ->get();
    }

    public function getWithQuery(
        array|callable $query,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): Collection {
        return $this->query()
            ->select($columns)
            ->where($query)
            ->with($relations)
            ->orderBy('id', $orderBy)
            ->get();
    }

    public function first(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): Builder|Model|null {
        return $this->query()
            ->select($columns)
            ->with($relations)
            ->where($byColumn, $value)
            ->first();
    }

    public function getFirst(): ?Model
    {
        return $this->query()->first();
    }

    public function create(array $payload): Model
    {
        return $this->query()->create($payload)->fresh();
    }

    public function insert(array $payload): bool
    {
        return $this->query()->insert($payload);
    }

    public function update(int|string $modelId, array $payload): bool
    {
        return $this->getById($modelId)->update($payload);
    }

    public function delete(int|string $modelId, array $filesFields = []): bool
    {
        $model = $this->getById($modelId);
        $filePaths = collect($filesFields)
            ->map(fn (string $field): mixed => $model->getAttribute($field))
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();

        $deleted = (bool) $model->delete();

        if ($deleted) {
            $this->safeDeleteFiles($filePaths, 'Repository model deletion', [
                'model' => $model::class,
                'model_id' => $model->getKey(),
            ]);
        }

        return $deleted;
    }

    public function paginate(
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator {
        return $this->query()
            ->select($columns)
            ->with($relations)
            ->orderBy('id', $orderBy)
            ->paginate($perPage);
    }

    public function paginateWithQuery(
        array|callable $query,
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator {
        return $this->query()
            ->select($columns)
            ->where($query)
            ->with($relations)
            ->orderBy('id', $orderBy)
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }
}
