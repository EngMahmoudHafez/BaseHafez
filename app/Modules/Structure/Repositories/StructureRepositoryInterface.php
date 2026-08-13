<?php

namespace App\Modules\Structure\Repositories;

use App\Modules\Base\Repositories\RepositoryInterface;
use App\Modules\Structure\Models\Structure;

interface StructureRepositoryInterface extends RepositoryInterface
{
    public function structure(string $key): ?Structure;

    /** @param array<string, mixed> $content */
    public function publish(string $key, array $content): Structure;
}
