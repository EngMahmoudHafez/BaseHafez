<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\LoginRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\SendResetLinkEmailRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\UpdatePasswordRequest;
use App\Modules\Auth\Http\Services\Dashboard\Auth\AuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function showLoginForm(): View
    {
        return view('auth::dashboard.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        return $this->auth->login($request);
    }

    public function logout(Request $request): RedirectResponse
    {
        return $this->auth->logout($request);
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth::dashboard.auth.forgot-password');
    }

    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): RedirectResponse
    {
        return $this->auth->sendResetLinkEmail($request);
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth::dashboard.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        return $this->auth->resetPassword($request);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        return $this->auth->updatePassword($request);
    }
}
