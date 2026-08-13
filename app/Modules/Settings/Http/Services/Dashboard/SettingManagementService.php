<?php

namespace App\Modules\Settings\Http\Services\Dashboard;

use App\Modules\Settings\Http\Requests\Dashboard\UpdateSettingRequest;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SettingManagementService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): View
    {
        return view('settings::dashboard.index', [
            'settings' => Setting::query()
                ->where('show_in_dashboard', true)
                ->orderBy('key')
                ->paginate(15),
        ]);
    }

    public function edit(Setting $setting): View
    {
        return view('settings::dashboard.edit', [
            'setting' => $setting,
        ]);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($setting, $data): void {
            $setting->update(['notes' => $data['notes'] ?? null]);

            // Route the value change through the SettingsService so the cached
            // `settings.all` map is invalidated in one place on every write.
            $this->settings->set($setting->key, $data['value'] ?? null);
        });

        return to_route('dashboard.settings.index')
            ->with('success', __('settings::settings.messages.updated'));
    }
}
