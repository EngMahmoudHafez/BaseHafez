<?php

namespace App\Modules\Auth\Http\Services\Api\V1\Auth;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\SignInRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\SignUpRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\User;
use App\Modules\Base\Http\Helpers\Http;
use App\Modules\Base\Http\Responses\ApiResponse;
use App\Modules\Base\Http\Services\PlatformService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService extends PlatformService
{
    public function __construct(
        private readonly AuthAccountService $accountService,
        private readonly AuthOtpService $otpService,
    ) {}

    public static function platform(): string
    {
        return 'api';
    }

    public function signUp(SignUpRequest $request): JsonResponse
    {
        $signUpInput = $request->validated();
        $country = $this->accountService->resolveCountry($signUpInput);
        $phone = $this->accountService->normalizeContactNumber($signUpInput['phone'], $country->dial_code);

        return DB::transaction(function () use ($country, $phone, $request, $signUpInput) {
            $user = $this->accountService->findUserByCountryPhone($country, $phone);

            if ($user && $this->accountService->isBlocked($user)) {
                return ApiResponse::error(Http::FORBIDDEN, __('messages.Account is inactive. Please contact the administration'));
            }

            if ($user && $this->accountService->isActive($user)) {
                return ApiResponse::error(Http::BAD_REQUEST, __('messages.Phone already exists'));
            }

            if (! empty($signUpInput['email']) && $this->accountService->emailAlreadyBelongsToAnotherUser($signUpInput['email'], $user?->id)) {
                return ApiResponse::error(Http::BAD_REQUEST, __('messages.Email already exists'));
            }

            $userAttributes = $this->accountService->pendingAuthUserAttributes($country, $signUpInput, $phone);
            $user = $this->accountService->createOrUpdatePendingAuthUser($user, $userAttributes);
            $otp = $this->otpService->issuePhone($user, 'register');

            Log::info('Auth sign-up OTP issued', [
                'user_id' => $user->id,
                'status' => $this->accountService->statusValue($user),
                'ip' => $request->ip(),
            ]);

            return ApiResponse::success(Http::CREATED, __('messages.created successfully'), [
                'user' => $this->accountService->userPayload($user->refresh(), $otp->token),
                'otp_token' => $otp->token,
                'expires_in_minutes' => 5,
                'verification' => $this->verificationPayload($otp),
            ]);
        });
    }

    public function verifySignUpOtp(VerifyOtpRequest $request): JsonResponse
    {
        $verificationInput = $request->validated();
        $otp = $this->otpService->verify(
            $verificationInput['otp_token'],
            $verificationInput['code'],
            ['register', 'login'],
            'phone',
        );

        if ($otp instanceof JsonResponse) {
            return $otp;
        }

        $user = User::query()->find($otp->user_id);

        if (! $user) {
            return ApiResponse::error(Http::NOT_FOUND, __('messages.User not found'));
        }

        if (! $this->otpIdentityMatchesAuthUser($verificationInput, $user)) {
            return ApiResponse::error(Http::BAD_REQUEST, __('messages.Verification data does not match the OTP'));
        }

        if ($this->accountService->isBlocked($user)) {
            return ApiResponse::error(Http::FORBIDDEN, __('messages.Account is inactive. Please contact the administration'));
        }

        if ($this->accountService->isPending($user)) {
            $user->forceFill(['status' => UserStatus::Active])->save();
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = JWTAuth::fromUser($user);

        Log::info('Auth OTP verified', [
            'user_id' => $user->id,
            'otp_id' => $otp->id,
            'purpose' => $otp->purpose,
            'ip' => $request->ip(),
        ]);

        return ApiResponse::success(Http::OK, __('messages.Login verified successfully'), [
            'user' => $this->accountService->userPayload($user->refresh(), token: $token),
            'token' => $token,
        ]);
    }

    public function resendSignUpOtp(ResendOtpRequest $request): JsonResponse
    {
        $resendInput = $request->validated();
        $otp = $this->otpService->resendPhone($resendInput['otp_token']);

        if ($otp instanceof JsonResponse) {
            return $otp;
        }

        return ApiResponse::success(
            Http::OK,
            __('messages.OTP_Is_Send'),
            [
                'otp_token' => $otp->token,
                'expires_in_minutes' => 5,
                'verification' => $this->verificationPayload($otp),
            ],
        );
    }

    public function signIn(SignInRequest $request): JsonResponse
    {
        $signInInput = $request->validated();
        $country = $this->accountService->resolveCountry($signInInput);
        $phone = $this->accountService->normalizeContactNumber($signInInput['phone'], $country->dial_code);

        $user = $this->accountService->findUserByCountryPhone($country, $phone);

        if (! $user) {
            return ApiResponse::error(Http::NOT_FOUND, __('messages.Phone not registered. Please create a new account'));
        }

        if ($error = $this->ensureUserCanLogin($user)) {
            return $error;
        }

        $otp = $this->otpService->issuePhone($user, 'login');

        Log::info('Auth sign-in OTP issued', [
            'user_id' => $user->id,
            'status' => $this->accountService->statusValue($user),
            'ip' => $request->ip(),
        ]);

        return ApiResponse::success(Http::CREATED, __('messages.OTP_Is_Send'), [
            'user' => $this->accountService->userPayload($user->refresh(), $otp->token),
            'otp_token' => $otp->token,
            'expires_in_minutes' => 5,
            'verification' => $this->verificationPayload($otp),
        ]);
    }

    public function signOut(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        JWTAuth::parseToken()->invalidate();

        if ($user) {
            Log::info('User signed out successfully', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
        }

        return ApiResponse::success(Http::OK, __('messages.Successfully loggedOut'));
    }

    /**
     * Returns a fail response if login should be rejected; otherwise null.
     */
    private function ensureUserCanLogin(User $user): ?JsonResponse
    {
        if ($this->accountService->isBlocked($user) || $this->accountService->isPending($user)) {
            return ApiResponse::error(Http::FORBIDDEN, __('messages.Account is inactive. Please contact the administration'));
        }

        return null;
    }

    /** @param array<string, mixed> $verificationInput */
    private function otpIdentityMatchesAuthUser(array $verificationInput, User $user): bool
    {
        $submittedIdentity = $verificationInput['whatsapp']
            ?? $verificationInput['username']
            ?? $verificationInput['identifier']
            ?? $verificationInput['phone']
            ?? null;

        if ($submittedIdentity === null || $submittedIdentity === '') {
            return true;
        }

        return in_array($submittedIdentity, [$user->whatsapp, $user->email, $user->phone], true);
    }

    /** @return array<string, mixed> */
    private function verificationPayload(AuthOtp $otp): array
    {
        $payload = [
            'otp_token' => $otp->token,
            'identifier' => $otp->identifier,
            'masked_identifier' => $this->accountService->maskedContact($otp->identifier),
            'channel' => $otp->channel,
            'code_length' => 4,
            'expires_at' => $otp->expires_at->toISOString(),
        ];

        if (app()->environment(['local', 'testing'])) {
            $payload['otp_code'] = $otp->getAttribute('otp_code');
        }

        return $payload;
    }
}
