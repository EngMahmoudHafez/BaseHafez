<?php

namespace App\Modules\Base\Repositories;

use Illuminate\Database\Eloquent\Model;

interface WritableRepositoryInterface
{
    /** @param array<string, mixed> $payload */
    public function create(array $payload): Model;

    /** @param array<string, mixed> $payload */
    public function insert(array $payload): bool;

    /** @param array<string, mixed> $payload */
    public function update(int|string $modelId, array $payload): bool;

    /** @param array<int, string> $filesFields */
    public function delete(int|string $modelId, array $filesFields = []): bool;
}
