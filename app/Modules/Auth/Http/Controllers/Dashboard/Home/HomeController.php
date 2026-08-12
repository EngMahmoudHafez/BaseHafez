<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Home;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Services\Dashboard\DashboardDataService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly DashboardDataService $dashboardDataService,
    ) {}

    public function index(): View
    {
        $manager = auth('manager')->user();

        return view('auth::dashboard.home', $this->dashboardDataService->statistics($manager));
    }
}
