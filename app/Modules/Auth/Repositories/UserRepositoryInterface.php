<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\User;
use App\Modules\Base\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function create(array $payload): User;

    public function getById(
        int|string $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = [],
    ): User;

    public function getActiveUsers(): Builder;

    public function findByPhone(string $phone, array $relations = []): ?User;

    public function findByEmail(string $email, array $relations = []): ?User;
}
