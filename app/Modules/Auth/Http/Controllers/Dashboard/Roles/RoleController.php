<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Roles;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\Role\RoleRequest;
use App\Modules\Auth\Http\Services\Dashboard\Roles\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles-read', ['index', 'show']),
            new Middleware('permission:roles-create', ['store', 'create']),
            new Middleware('permission:roles-update', ['update', 'edit']),
            new Middleware('permission:roles-delete', ['destroy']),
            new Middleware('permission:managers-read', ['managers']),
        ];
    }

    public function __construct(private readonly RoleService $service) {}

    public function index(Request $request): View
    {
        return $this->service->index($request);
    }

    public function managers(int $id): RedirectResponse
    {
        return $this->service->managers($id);
    }

    public function create(): View
    {
        return $this->service->create();
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        return $this->service->store($request);
    }

    public function show(int $id): View
    {
        return $this->service->show($id);
    }

    public function edit(int $id): View
    {
        return $this->service->edit($id);
    }

    public function update(RoleRequest $request, int $id): RedirectResponse
    {
        return $this->service->update($request, $id);
    }

    public function destroy(int $id): RedirectResponse
    {
        return $this->service->destroy($id);
    }
}
