<?php

namespace App\Modules\Auth\Http\Services\Api\V1\Auth\Password;

use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\ForgetPasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\PasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\Password\VerifyOtpRequest;
use App\Modules\Auth\Http\Services\Api\V1\Auth\AuthOtpService;
use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Repositories\UserRepositoryInterface;
use App\Modules\Base\Http\Helpers\Http;
use App\Modules\Base\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordService
{
    private const OTP_PURPOSE = 'password-reset';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuthOtpService $otpService,
    ) {}

    public function forgot(ForgetPasswordRequest $request): JsonResponse
    {
        $email = $this->normalizedEmail($request->validated('email'));
        $user = $this->users->findByEmail($email);
        $otp = $user ? $this->otpService->issueEmail($user, self::OTP_PURPOSE) : null;

        $data = [
            'otp_token' => $otp->token ?? Str::random(64),
            'expires_in_minutes' => 5,
        ];

        if ($otp && app()->environment(['local', 'testing'])) {
            $data['verification'] = ['otp_code' => $otp->getAttribute('otp_code')];
        }

        return ApiResponse::success(Http::OK, __('messages.OTP_Is_Send'), $data);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $email = $this->normalizedEmail($data['email']);
        $user = $this->users->findByEmail($email);

        if (! $user) {
            return $this->invalidOtpResponse();
        }

        $otp = $this->otpService->verify(
            $data['otp_token'],
            $data['otp'],
            self::OTP_PURPOSE,
            'email',
        );

        if ($otp instanceof JsonResponse) {
            return $otp;
        }

        if (! $this->otpBelongsToUser($otp, $user, $email)) {
            return $this->invalidOtpResponse();
        }

        $resetToken = Str::random(64);
        Cache::put(
            $this->resetCacheKey($user),
            hash('sha256', $resetToken),
            now()->addMinutes(10),
        );

        return ApiResponse::success(Http::OK, __('messages.OTP verified successfully'), [
            'reset_token' => $resetToken,
            'expires_in_minutes' => 10,
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->users->findByEmail($this->normalizedEmail($data['email']));
        // Atomically consume the reset token (get-and-delete) so it is single-use
        // even under concurrent requests.
        $cachedTokenHash = $user ? Cache::pull($this->resetCacheKey($user)) : null;

        if (! $user || ! is_string($cachedTokenHash) || ! hash_equals(
            $cachedTokenHash,
            hash('sha256', $data['reset_token']),
        )) {
            return ApiResponse::error(Http::BAD_REQUEST, __('messages.Invalid or expired reset token'));
        }

        DB::transaction(function () use ($data, $user): void {
            $user->forceFill([
                'password' => $data['password'],
                // Revoke every token issued before this reset.
                'token_version' => $user->tokenVersion() + 1,
            ])->save();
        });

        return ApiResponse::success(Http::OK, __('messages.Password reset successfully'));
    }

    public function updatePassword(PasswordRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        if (! $user instanceof User) {
            return ApiResponse::error(Http::UNAUTHORIZED, __('messages.Unauthenticated'));
        }

        $newPassword = $request->validated('new_password');

        if (Hash::check($newPassword, (string) $user->password)) {
            return ApiResponse::error(
                Http::UNPROCESSABLE_ENTITY,
                __('messages.The new password must be different from the current password'),
            );
        }

        $user->forceFill([
            'password' => $newPassword,
            // Revoke every token issued before this change (this request already passed).
            'token_version' => $user->tokenVersion() + 1,
        ])->save();

        return ApiResponse::success(Http::OK, __('messages.updated successfully'));
    }

    private function otpBelongsToUser(AuthOtp $otp, User $user, string $email): bool
    {
        return (int) $otp->user_id === (int) $user->id
            && $otp->channel === 'email'
            && hash_equals($email, $this->normalizedEmail($otp->identifier));
    }

    private function normalizedEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function resetCacheKey(User $user): string
    {
        return 'auth:password-reset:' . $user->getKey();
    }

    private function invalidOtpResponse(): JsonResponse
    {
        return ApiResponse::error(Http::BAD_REQUEST, __('messages.Wrong OTP code or expired'));
    }
}
