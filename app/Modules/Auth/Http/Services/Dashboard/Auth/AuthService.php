<?php

namespace App\Modules\Auth\Http\Services\Dashboard\Auth;

use App\Modules\Auth\Http\Requests\Dashboard\Auth\LoginRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\SendResetLinkEmailRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\UpdatePasswordRequest;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(
        private readonly ManagerRepositoryInterface $managerRepository,
        private readonly ManagerPasswordResetService $passwordResetService,
    ) {}

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        // Attempt first so an unknown email, a wrong password, and an inactive
        // account are indistinguishable until the correct password is supplied —
        // no account enumeration from the login form.
        if (! auth('manager')->attempt($credentials, $request->boolean('remember_me'))) {
            $this->logLoginAttempt('Failed manager login attempt', $request, $credentials['email']);

            return to_route('login')->with('error', __('messages.Incorrect email or password'));
        }

        $manager = auth('manager')->user();

        if ($manager instanceof Manager && ! $manager->isActiveAccount()) {
            auth('manager')->logout();
            $this->logLoginAttempt('Inactive manager login attempt', $request, $credentials['email']);

            return to_route('login')->with('error', __('messages.Account is inactive. Please contact the administration'));
        }

        $request->session()->regenerate();
        $this->logLoginAttempt('Manager logged in', $request, $credentials['email']);

        return to_route('dashboard.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth('manager')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): RedirectResponse
    {
        return $this->passwordResetService->sendResetLinkEmail($request);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        return $this->passwordResetService->resetPassword($request);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $manager = auth('manager')->user();

        abort_unless($manager instanceof Manager, 403);

        if (! Hash::check($request->string('current_password'), $manager->password)) {
            return back()->with('error', __('messages.Old_Password_Wrong'));
        }

        $this->managerRepository->update($manager->id, [
            'password' => Hash::make($request->string('new_password')),
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    private function logLoginAttempt(string $message, LoginRequest $request, string $email): void
    {
        Log::info($message, [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
