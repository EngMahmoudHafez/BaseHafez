<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\User;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\User\UserRequest;
use App\Modules\Auth\Http\Services\Dashboard\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users-read', ['index', 'show']),
            new Middleware('permission:users-create', ['store', 'create']),
            new Middleware('permission:users-update', ['update', 'edit', 'toggle']),
            new Middleware('permission:users-delete', ['destroy']),
            new Middleware('permission:users-export', ['export']),
        ];
    }

    public function __construct(private readonly UserService $user) {}

    public function index(Request $request): View
    {
        return $this->user->index($request);
    }

    public function show(int $id): View
    {
        return $this->user->show($id);
    }

    public function create(): View
    {
        return $this->user->create();
    }

    public function store(UserRequest $request): RedirectResponse
    {
        return $this->user->store($request);
    }

    public function edit(int $id): View
    {
        return $this->user->edit($id);
    }

    public function update(UserRequest $request, int $id): RedirectResponse
    {
        return $this->user->update($request, $id);
    }

    public function destroy(int $id): RedirectResponse
    {
        return $this->user->destroy($id);
    }

    public function toggle(int $id): RedirectResponse
    {
        return $this->user->toggle($id);
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->user->export($request);
    }
}
