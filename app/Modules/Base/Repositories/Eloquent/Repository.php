<?php

namespace App\Modules\Base\Repositories\Eloquent;

use App\Modules\Base\Concerns\FileTrait;
use App\Modules\Base\Repositories\RepositoryInterface;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * Larastan's query-builder return-type extensions collapse the class template
 * (`TModel`) to its `Model` bound inside this generic base, so the reads below
 * carry an inline `@var` to restore the true `Builder<TModel>` /
 * `Collection<int, TModel>`. Subclasses (`@extends Repository<Concrete>`) then
 * receive fully-typed returns with no casts of their own.
 */
abstract class Repository implements RepositoryInterface
{
    use FileTrait;

    /** @var TModel */
    protected Model $model;

    /** @param TModel $model */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /** @return TModel */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return Collection<int, TModel>
     */
    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        /** @var Collection<int, TModel> $models */
        $models = $this->query()->with($relations)->get($columns);

        return $models;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $appends
     * @return TModel
     */
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

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return Collection<int, TModel>
     */
    public function get(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): Collection {
        /** @var Collection<int, TModel> $models */
        $models = $this->query()
            ->select($columns)
            ->with($relations)
            ->where($byColumn, $value)
            ->get();

        return $models;
    }

    /**
     * @param  array<string, mixed>|Closure  $query
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return Collection<int, TModel>
     */
    public function getWithQuery(
        array|Closure $query,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): Collection {
        /** @var Collection<int, TModel> $models */
        $models = $this->query()
            ->select($columns)
            ->where($query)
            ->with($relations)
            ->orderBy('id', $this->direction($orderBy))
            ->get();

        return $models;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return TModel|null
     */
    public function first(
        string $byColumn,
        mixed $value,
        array $columns = ['*'],
        array $relations = [],
    ): ?Model {
        return $this->query()
            ->select($columns)
            ->with($relations)
            ->where($byColumn, $value)
            ->first();
    }

    /** @return TModel|null */
    public function getFirst(): ?Model
    {
        return $this->query()->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return TModel
     */
    public function create(array $payload): Model
    {
        return $this->query()->create($payload)->refresh();
    }

    /** @param array<string, mixed> $payload */
    public function insert(array $payload): bool
    {
        return $this->query()->insert($payload);
    }

    /** @param array<string, mixed> $payload */
    public function update(int|string $modelId, array $payload): bool
    {
        return $this->getById($modelId)->update($payload);
    }

    /** @param array<int, string> $filesFields */
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

    /**
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator {
        /** @var LengthAwarePaginator<int, TModel> $paginator */
        $paginator = $this->query()
            ->select($columns)
            ->with($relations)
            ->orderBy('id', $this->direction($orderBy))
            ->paginate($perPage);

        return $paginator;
    }

    /**
     * @param  array<string, mixed>|Closure  $query
     * @param  array<int, string>  $relations
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginateWithQuery(
        array|Closure $query,
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        array $columns = ['*'],
    ): LengthAwarePaginator {
        /** @var LengthAwarePaginator<int, TModel> $paginator */
        $paginator = $this->query()
            ->select($columns)
            ->where($query)
            ->with($relations)
            ->orderBy('id', $this->direction($orderBy))
            ->paginate($perPage);

        return $paginator;
    }

    /** @return Builder<TModel> */
    public function query(): Builder
    {
        /** @var Builder<TModel> $query */
        $query = $this->model->newQuery();

        return $query;
    }

    /**
     * Normalize a caller-supplied sort keyword to a valid SQL direction.
     *
     * @return 'asc'|'desc'
     */
    private function direction(string $orderBy): string
    {
        return strtolower($orderBy) === 'desc' ? 'desc' : 'asc';
    }
}
