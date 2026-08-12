<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Managers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\StoreManagerRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\UpdateManagerPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\UpdateManagerRequest;
use App\Modules\Auth\Http\Services\Dashboard\Manager\ManagerService;
use App\Modules\Auth\Models\Manager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ManagerService $managerService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:managers-read', only: ['index', 'show']),
            new Middleware('permission:managers-create', only: ['store', 'create', 'createWithRole']),
            new Middleware('permission:managers-update', only: ['update', 'edit', 'toggle', 'editPassword', 'updatePassword']),
            new Middleware('permission:managers-delete', only: ['destroy']),
            new Middleware('permission:managers-export', only: ['export']),
        ];
    }

    public function index(Request $request): View
    {
        return $this->managerService->index($request);
    }

    public function create(): View
    {
        return $this->managerService->create();
    }

    public function createWithRole(int $role): View
    {
        return $this->managerService->create($role);
    }

    public function store(StoreManagerRequest $request): RedirectResponse
    {
        return $this->managerService->store($request);
    }

    public function show(Manager $manager): View
    {
        return $this->managerService->show($manager);
    }

    public function edit(Manager $manager): View
    {
        return $this->managerService->edit($manager);
    }

    public function update(UpdateManagerRequest $request, Manager $manager): RedirectResponse
    {
        return $this->managerService->update($request, $manager);
    }

    public function editPassword(Manager $manager): View
    {
        return $this->managerService->editPassword($manager);
    }

    public function updatePassword(UpdateManagerPasswordRequest $request, Manager $manager): RedirectResponse
    {
        return $this->managerService->updatePassword($request, $manager);
    }

    public function toggle(Manager $manager): RedirectResponse
    {
        return $this->managerService->toggle($manager);
    }

    public function destroy(Manager $manager): RedirectResponse
    {
        return $this->managerService->destroy($manager);
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->managerService->export($request);
    }
}
