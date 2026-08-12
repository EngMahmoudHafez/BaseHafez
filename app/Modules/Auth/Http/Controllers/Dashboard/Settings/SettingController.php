<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\UpdatePasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Settings\InfoSettingsRequest;
use App\Modules\Auth\Http\Services\Dashboard\Auth\AuthService;
use App\Modules\Auth\Http\Services\Dashboard\Settings\SettingsService;
use App\Modules\Auth\Models\Manager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly AuthService $auth,
    ) {}

    public function edit(): View
    {
        return view('auth::dashboard.profile.edit', ['manager' => $this->manager()]);
    }

    public function update(InfoSettingsRequest $request): RedirectResponse
    {
        return $this->settings->update($this->manager(), $request);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        return $this->auth->updatePassword($request);
    }

    private function manager(): Manager
    {
        $manager = auth('manager')->user();

        abort_unless($manager instanceof Manager, 403);

        return $manager;
    }
}
