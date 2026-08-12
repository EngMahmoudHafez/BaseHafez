<?php

namespace App\Modules\Structure\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Http\Services\Api\V1\StructureService;
use App\Modules\Structure\Support\SectionRegistry;
use Illuminate\Http\JsonResponse;

/**
 * Returns the published content of a single public content page by its key,
 * e.g. GET /web/v1/structures/hero. Unknown or non-public keys return 404.
 */
class StructurePageController extends Controller
{
    public function __construct(
        private readonly StructureService $structures,
    ) {}

    public function __invoke(string $key): JsonResponse
    {
        abort_unless(SectionRegistry::isPublic($key), 404);

        return $this->structures->get($key);
    }
}
