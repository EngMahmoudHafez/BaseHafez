<?php

namespace App\Modules\Auth\Http\Middleware;

use App\Modules\Auth\Models\Manager;
use App\Modules\Base\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckManagerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $manager = auth('manager')->user();

        abort_unless($manager instanceof Manager, 401);

        if ($manager->isActiveAccount()) {
            return $next($request);
        }

        auth('manager')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return ApiResponse::error(403, __('messages.Account is inactive. Please contact the administration'));
        }

        return to_route('login')->with('error', __('messages.Account is inactive. Please contact the administration'));
    }
}
