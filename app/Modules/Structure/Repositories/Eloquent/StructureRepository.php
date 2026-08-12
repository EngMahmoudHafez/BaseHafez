<?php

namespace App\Modules\Structure\Repositories\Eloquent;

use App\Modules\Base\Repositories\Eloquent\Repository;
use App\Modules\Structure\Models\Structure;
use App\Modules\Structure\Repositories\StructureRepositoryInterface;
use App\Modules\Structure\Support\SectionContentSanitizer;

class StructureRepository extends Repository implements StructureRepositoryInterface
{
    public function __construct(Structure $model, private readonly SectionContentSanitizer $sanitizer)
    {
        parent::__construct($model);
    }

    public function structure(string $key): ?Structure
    {
        return $this->model::query()->where('key', $key)->first();
    }

    public function publish(string $key, array $content): Structure
    {
        return $this->model::query()->updateOrCreate(
            ['key' => $key],
            ['content' => $this->sanitizer->sanitize($key, $content)],
        );
    }
}
