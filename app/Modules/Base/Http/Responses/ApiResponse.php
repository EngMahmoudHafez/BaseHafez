<?php

namespace App\Modules\Base\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * The single source of truth for API response shape across the base.
 *
 * Envelope: {status, message, data, pagination?}. `data` is normalized for
 * JsonResource, ResourceCollection, and LengthAwarePaginator so every
 * controller and service emits the exact same structure.
 */
final class ApiResponse
{
    public static function success(int $status = 200, string $message = 'Success', mixed $data = []): JsonResponse
    {
        return response()->json(self::envelope($status, $message, $data), $status);
    }

    public static function error(int $status = 422, string $message = 'Failed', mixed $data = []): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @return array{status:int,message:string,data:mixed,pagination?:array<string,int>}
     */
    private static function envelope(int $status, string $message, mixed $data): array
    {
        $request = request();
        $payload = ['status' => $status, 'message' => $message];

        if ($data instanceof ResourceCollection) {
            $payload['data'] = $data->toArray($request);
            $pagination = $data->with($request)['pagination'] ?? self::paginationFor($data->resource);

            if ($pagination !== null) {
                $payload['pagination'] = $pagination;
            }

            return $payload;
        }

        if ($data instanceof LengthAwarePaginator) {
            $payload['data'] = $data->items();
            $payload['pagination'] = self::pagination($data);

            return $payload;
        }

        if ($data instanceof JsonResource) {
            $payload['data'] = $data->resolve($request);

            return $payload;
        }

        $payload['data'] = $data;

        return $payload;
    }

    /**
     * @return array<string,int>|null
     */
    private static function paginationFor(mixed $resource): ?array
    {
        return $resource instanceof LengthAwarePaginator ? self::pagination($resource) : null;
    }

    /**
     * @return array<string,int>
     */
    private static function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
