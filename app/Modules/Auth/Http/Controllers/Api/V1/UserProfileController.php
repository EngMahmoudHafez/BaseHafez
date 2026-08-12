<?php

namespace App\Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\V1\UserProfile\UpdateProfileRequest;
use App\Modules\Auth\Http\Requests\Api\V1\UserProfile\UploadProfileImageRequest;
use App\Modules\Auth\Http\Services\Api\V1\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $profileService,
    ) {}

    /**
     * Get authenticated user's profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        return $this->profileService->getProfile((int) $request->user()->getAuthIdentifier());
    }

    /**
     * Update authenticated user's profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->profileService->updateProfile(
            (int) $request->user()->getAuthIdentifier(),
            $request->validated(),
            $request->file('profile_image'),
        );
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(UploadProfileImageRequest $request): JsonResponse
    {
        return $this->profileService->uploadProfileImage(
            (int) $request->user()->getAuthIdentifier(),
            $request->file('profile_image'),
        );
    }
}
