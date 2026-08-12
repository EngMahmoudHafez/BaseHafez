<?php

namespace App\Modules\Structure\Http\Services\Api\V1;

use App\Modules\Base\Http\Responses\ApiResponse;
use App\Modules\Structure\Repositories\StructureRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class StructureService
{
    public function __construct(
        private readonly StructureRepositoryInterface $structures,
    ) {}

    /** @param class-string<JsonResource>|null $resourceClass */
    public function get(string $key, ?string $resourceClass = null): JsonResponse
    {
        $content = $this->content($key);

        if ($content === []) {
            return ApiResponse::success(message: __('messages.structure.not_found', ['key' => $key]));
        }

        $data = $resourceClass === null ? $content : new $resourceClass($content);

        return ApiResponse::success(data: $data);
    }

    public function content(string $key): array
    {
        $content = $this->structures->structure($key)?->content ?? [];

        return $content[app()->getLocale()] ?? $content['ar'] ?? $content['en'] ?? [];
    }
}
