<?php

namespace App\Modules\Auth\Http\Controllers\Api\V1\Auth\Password;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\ForgetPasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\PasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\VerifyOtpRequest;
use App\Modules\Auth\Http\Services\Api\V1\Auth\Password\PasswordService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(private readonly PasswordService $service) {}

    public function forgot(ForgetPasswordRequest $request): JsonResponse
    {
        return $this->service->forgot($request);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->service->verifyOtp($request);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        return $this->service->reset($request);
    }

    public function updatePassword(PasswordRequest $request): JsonResponse
    {
        return $this->service->updatePassword($request);
    }
}
