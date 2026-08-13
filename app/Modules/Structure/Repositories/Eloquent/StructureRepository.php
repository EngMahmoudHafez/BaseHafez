<?php

namespace App\Modules\Structure\Repositories\Eloquent;

use App\Modules\Base\Repositories\Eloquent\Repository;
use App\Modules\Structure\Models\Structure;
use App\Modules\Structure\Repositories\StructureRepositoryInterface;
use App\Modules\Structure\Support\SectionContentSanitizer;

/**
 * @extends Repository<Structure>
 */
class StructureRepository extends Repository implements StructureRepositoryInterface
{
    public function __construct(Structure $model, private readonly SectionContentSanitizer $sanitizer)
    {
        parent::__construct($model);
    }

    public function structure(string $key): ?Structure
    {
        return $this->query()->where('key', $key)->first();
    }

    /** @param array<string, mixed> $content */
    public function publish(string $key, array $content): Structure
    {
        return $this->query()->updateOrCreate(
            ['key' => $key],
            ['content' => $this->sanitizer->sanitize($key, $content)],
        );
    }
}
