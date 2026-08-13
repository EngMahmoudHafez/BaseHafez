<?php

namespace App\Modules\Notifications\Http\Resources\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    public $collects = NotificationResource::class;

    /** @return array<int, mixed> */
    public function toArray(Request $request): array
    {
        return $this->collection?->toArray() ?? [];
    }

    /** @return array<string, mixed> */
    public function with(Request $request): array
    {
        if (! $this->resource instanceof LengthAwarePaginator) {
            return [];
        }

        return [
            'pagination' => [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
            ],
        ];
    }
}
