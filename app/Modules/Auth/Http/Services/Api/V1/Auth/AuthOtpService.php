<?php

namespace App\Modules\Auth\Http\Services\Api\V1\Auth;

use App\Modules\Auth\Mail\OtpEmail;
use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\User;
use App\Modules\Base\Http\Helpers\Http;
use App\Modules\Base\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AuthOtpService
{
    private const EXPIRES_IN_MINUTES = 5;

    private const MAX_ATTEMPTS = 5;

    public function issuePhone(User $user, string $purpose): AuthOtp
    {
        $identifier = (string) ($user->phone ?: $user->whatsapp);
        [$otp, $code] = $this->create($user, $identifier, 'phone', $purpose);

        return $this->attachDebugCode($otp, $code);
    }

    public function issueEmail(User $user, string $purpose): AuthOtp
    {
        $identifier = (string) $user->email;
        [$otp, $code] = $this->create($user, $identifier, 'email', $purpose);

        try {
            // Queue (not send) so the forgot-password request returns immediately
            // and does not block on SMTP — this also removes the known-vs-unknown
            // email timing oracle.
            Mail::to($identifier)->queue(new OtpEmail(
                $code,
                $user->name,
            ));
        } catch (Throwable $exception) {
            $otp->delete();

            throw $exception;
        }

        return $this->attachDebugCode($otp, $code);
    }

    /** @param string|array<int, string>|null $purposes */
    public function verify(
        string $otpToken,
        string $code,
        string|array|null $purposes = null,
        ?string $channel = null,
    ): AuthOtp|JsonResponse {
        $otp = AuthOtp::query()
            ->where('token', $otpToken)
            ->whereNull('verified_at')
            ->when(
                is_string($purposes),
                fn ($query) => $query->where('purpose', $purposes),
            )
            ->when(
                is_array($purposes),
                fn ($query) => $query->whereIn('purpose', $purposes),
            )
            ->when($channel, fn ($query) => $query->where('channel', $channel))
            ->first();

        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= self::MAX_ATTEMPTS) {
            return $this->invalidOtpResponse();
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return $this->invalidOtpResponse();
        }

        // Atomic single-use consume: only the first of any concurrent verifications
        // flips verified_at, so an OTP can never be verified twice.
        $consumed = AuthOtp::query()
            ->whereKey($otp->id)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        if ($consumed === 0) {
            return $this->invalidOtpResponse();
        }

        $otp->setAttribute('verified_at', now());

        return $otp;
    }

    public function resendPhone(string $otpToken): AuthOtp|JsonResponse
    {
        $existingOtp = AuthOtp::query()
            ->with('user')
            ->where('token', $otpToken)
            ->where('channel', 'phone')
            ->whereIn('purpose', ['register', 'login'])
            ->whereNull('verified_at')
            ->first();

        if (! $existingOtp || ! $existingOtp->user || $existingOtp->expires_at->isPast()) {
            return ApiResponse::error(Http::BAD_REQUEST, __('messages.Invalid or expired OTP token'));
        }

        return $this->issuePhone($existingOtp->user, $existingOtp->purpose);
    }

    /**
     * @return array{AuthOtp, string}
     */
    private function create(User $user, string $identifier, string $channel, string $purpose): array
    {
        AuthOtp::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->delete();

        $code = $this->generateCode();
        $otp = AuthOtp::query()->create([
            'user_id' => $user->id,
            'country_id' => $user->country_id,
            'token' => $this->newOtpToken(),
            'identifier' => $identifier,
            'code_hash' => Hash::make($code),
            'channel' => $channel,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
            'attempts' => 0,
            'verified_at' => null,
        ]);

        Log::info('Authentication OTP issued', [
            'otp_id' => $otp->id,
            'user_id' => $user->id,
            'purpose' => $purpose,
            'channel' => $channel,
        ]);

        return [$otp, $code];
    }

    private function newOtpToken(): string
    {
        do {
            $token = Str::random(64);
        } while (AuthOtp::query()->where('token', $token)->exists());

        return $token;
    }

    private function generateCode(): string
    {
        if (app()->environment(['local', 'testing'])) {
            return '1111';
        }

        return (string) random_int(1000, 9999);
    }

    private function attachDebugCode(AuthOtp $otp, string $code): AuthOtp
    {
        if (app()->environment(['local', 'testing'])) {
            $otp->setAttribute('otp_code', $code);
        }

        return $otp;
    }

    private function invalidOtpResponse(): JsonResponse
    {
        return ApiResponse::error(Http::BAD_REQUEST, __('messages.Wrong OTP code or expired'));
    }
}
