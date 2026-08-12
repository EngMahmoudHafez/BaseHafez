<?php

namespace App\Modules\Auth\Http\Services\Api\V1;

use App\Modules\Auth\Http\Resources\V1\UserProfileResource;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Repositories\UserRepositoryInterface;
use App\Modules\Base\Http\Helpers\Http;
use App\Modules\Base\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class UserProfileService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function getProfile(int $userId): JsonResponse
    {
        $user = $this->users->getById($userId, relations: ['country']);

        return ApiResponse::success(
            Http::OK,
            __('messages.Profile retrieved successfully'),
            new UserProfileResource($user),
        );
    }

    public function updateProfile(
        int $userId,
        array $validatedInput,
        ?UploadedFile $profileImage = null,
    ): JsonResponse {
        $user = $this->users->getById($userId);
        $attributes = $this->normalizePhone($validatedInput);
        unset($attributes['profile_image'], $attributes['country_code']);

        if ($attributes !== []) {
            $this->users->update($userId, $attributes);
        }

        if ($profileImage) {
            $user->putImage($profileImage);
        }

        $updatedUser = $this->users->getById($userId, relations: ['country']);

        return ApiResponse::success(
            Http::OK,
            __('messages.Profile updated successfully'),
            new UserProfileResource($updatedUser),
        );
    }

    public function uploadProfileImage(int $userId, UploadedFile $file): JsonResponse
    {
        $user = $this->users->getById($userId);
        $user->putImage($file);

        return ApiResponse::success(Http::OK, __('messages.Profile image uploaded successfully'), [
            'path' => $user->avatar,
            'url' => $user->image_url,
            'profile_image_url' => $user->image_url,
        ]);
    }

    private function normalizePhone(array $attributes): array
    {
        if (! isset($attributes['phone'], $attributes['country_code'])) {
            unset($attributes['phone']);

            return $attributes;
        }

        $country = Country::query()
            ->where('dial_code', $attributes['country_code'])
            ->firstOrFail();
        $phone = $country->dial_code . ltrim((string) $attributes['phone'], '0');

        return [
            ...$attributes,
            'country_id' => $country->id,
            'phone' => $phone,
            'whatsapp' => $phone,
        ];
    }
}
