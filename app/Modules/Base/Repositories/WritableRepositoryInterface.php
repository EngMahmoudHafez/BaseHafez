<?php

namespace App\Modules\Base\Repositories;

use Illuminate\Database\Eloquent\Model;

interface WritableRepositoryInterface
{
    public function create(array $payload): Model;

    public function insert(array $payload): bool;

    public function update(int|string $modelId, array $payload): bool;

    public function delete(int|string $modelId, array $filesFields = []): bool;
}
