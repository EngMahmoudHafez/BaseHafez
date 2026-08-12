<?php

namespace App\Modules\Auth\Http\Services\Dashboard\Auth;

use App\Modules\Auth\Http\Requests\Dashboard\Auth\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\SendResetLinkEmailRequest;
use App\Modules\Auth\Notifications\ManagerResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ManagerPasswordResetService
{
    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $status = Password::broker('managers')->sendResetLink(
            $payload,
            fn ($manager, string $token) => $manager->notify(new ManagerResetPasswordNotification($token)),
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Manager reset link sent', ['email' => $payload['email'], 'ip' => $request->ip()]);
        } else {
            Log::warning('Manager reset link not sent', ['email' => $payload['email'], 'status' => $status, 'ip' => $request->ip()]);
        }

        // Always return the same response regardless of whether the email exists,
        // so the form cannot be used to enumerate manager accounts.
        return back()->with('status', __('auth.reset_link_sent'));
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $status = Password::broker('managers')->reset(
            $payload,
            function ($manager, string $password): void {
                $manager->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            Log::info('Manager password reset', ['email' => $payload['email'], 'ip' => $request->ip()]);

            return to_route('login')->with('status', __('auth.password_reset_success'));
        }

        return back()
            ->withErrors(['email' => __('auth.password_reset_failed')])
            ->withInput($request->only('email'));
    }
}
