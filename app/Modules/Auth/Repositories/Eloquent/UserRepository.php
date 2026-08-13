<?php

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Repositories\UserRepositoryInterface;
use App\Modules\Base\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Repository<User>
 */
class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload): User
    {
        return parent::create($payload);
    }

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
    ): User {
        return parent::getById($modelId, $columns, $relations, $appends);
    }

    /** @return Builder<User> */
    public function getActiveUsers(): Builder
    {
        return $this->query()->where('status', UserStatus::Active->value);
    }

    /** @param array<int, string> $relations */
    public function findByPhone(string $phone, array $relations = []): ?User
    {
        return $this->query()->with($relations)->where('phone', $phone)->first();
    }

    /** @param array<int, string> $relations */
    public function findByEmail(string $email, array $relations = []): ?User
    {
        return $this->query()->with($relations)->where('email', $email)->first();
    }
}
