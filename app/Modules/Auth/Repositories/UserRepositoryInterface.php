<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\User;
use App\Modules\Base\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface extends RepositoryInterface
{
    /** @param array<string, mixed> $payload */
    public function create(array $payload): User;

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
    ): User;

    /** @return Builder<User> */
    public function getActiveUsers(): Builder;

    /** @param array<int, string> $relations */
    public function findByPhone(string $phone, array $relations = []): ?User;

    /** @param array<int, string> $relations */
    public function findByEmail(string $email, array $relations = []): ?User;
}
