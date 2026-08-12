<?php

namespace App\Modules\Structure\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Http\Services\Dashboard\ContactMessageService;
use App\Modules\Structure\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ContactMessageController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly ContactMessageService $service,
    ) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:contact-messages-read', only: ['index', 'show']),
            new Middleware('permission:contact-messages-update', only: ['toggleRead']),
            new Middleware('permission:contact-messages-delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        return $this->service->index($request);
    }

    public function show(ContactMessage $contactMessage): View
    {
        return $this->service->show($contactMessage);
    }

    public function toggleRead(ContactMessage $contactMessage): RedirectResponse
    {
        return $this->service->toggleRead($contactMessage);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        return $this->service->destroy($contactMessage);
    }
}
