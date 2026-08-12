<?php

namespace App\Modules\Auth\Http\Services\Dashboard\Settings;

use App\Modules\Auth\Http\Requests\Dashboard\Settings\InfoSettingsRequest;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use Illuminate\Http\RedirectResponse;

class SettingsService
{
    public function __construct(
        private readonly ManagerRepositoryInterface $managers,
    ) {}

    public function update(Manager $manager, InfoSettingsRequest $request): RedirectResponse
    {
        $this->managers->update($manager->id, $request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $manager->putImage($request->file('image'));
        }

        return back()->with('success', __('messages.updated_successfully'));
    }
}
