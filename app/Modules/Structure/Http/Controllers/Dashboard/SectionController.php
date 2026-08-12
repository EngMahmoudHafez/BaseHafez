<?php

namespace App\Modules\Structure\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Http\Requests\Dashboard\SectionRequest;
use App\Modules\Structure\Http\Services\Dashboard\SectionManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The single dashboard controller for every content section. The concrete
 * section is bound to the route as the `section` default; the service renders and
 * persists it from the registry definition. Replaces the abstract
 * StructureController plus the sixteen thin per-section controllers.
 */
final class SectionController extends Controller
{
    public function __construct(
        private readonly SectionManagementService $sections,
    ) {}

    public function index(Request $request): View
    {
        return $this->sections->index((string) $request->route('section'));
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        return $this->sections->store($request);
    }
}
