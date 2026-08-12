<?php

namespace App\Modules\Auth\Http\Middleware;

use App\Modules\Auth\Models\User;
use App\Modules\Base\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTGuard;

/**
 * Enforces, on every authenticated API request, two invariants the stateless JWT
 * cannot express on its own:
 *  1. the user is still active — so blocking a user takes effect immediately
 *     instead of only when their token's TTL expires; and
 *  2. the token's `tv` claim matches the user's current `token_version` — so a
 *     password change or reset revokes every previously issued token.
 */
class EnsureApiUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (! $user instanceof User) {
            return ApiResponse::error(401, __('errors.unauthenticated'));
        }

        if (! $user->isActiveAccount()) {
            return ApiResponse::error(403, __('messages.Account is inactive. Please contact the administration'));
        }

        $guard = auth('api');
        $tokenVersion = $guard instanceof JWTGuard ? (int) $guard->payload()->get('tv') : -1;

        if ($tokenVersion !== $user->tokenVersion()) {
            return ApiResponse::error(401, __('errors.unauthenticated'));
        }

        return $next($request);
    }
}
