<?php

namespace App\Modules\Settings\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\Dashboard\UpdateSettingRequest;
use App\Modules\Settings\Http\Services\Dashboard\SettingManagementService;
use App\Modules\Settings\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly SettingManagementService $service,
    ) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings-read', only: ['index', 'edit']),
            new Middleware('permission:settings-update', only: ['update']),
        ];
    }

    public function index(): View
    {
        return $this->service->index();
    }

    public function edit(Setting $setting): View
    {
        return $this->service->edit($setting);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        return $this->service->update($request, $setting);
    }
}
