<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Repositories\UserRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends Repository implements UserRepositoryInterface
{
    protected Model $model;

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function create(array $payload): User
    {
        return $this->model->newQuery()->create($payload)->fresh();
    }

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): User {
        return $this->model->newQuery()
            ->select($columns)
            ->with($relations)
            ->findOrFail($modelId)
            ->append($appends);
    }

    public function getActiveUsers(): Builder
    {
        return $this->model::query()->where('status', UserStatus::Active->value);
    }

    public function findByPhone(string $phone, array $relations = []): ?User
    {
        return $this->model::with($relations)->where('phone', $phone)->first();
    }

    public function findByEmail(string $email, array $relations = []): ?User
    {
        return $this->model::with($relations)->where('email', $email)->first();
    }
}
