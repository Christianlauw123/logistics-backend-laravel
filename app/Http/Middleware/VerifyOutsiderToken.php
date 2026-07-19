<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOutsiderToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Retrieve the expected secret from the environment config
        $expectedToken = config('services.outsider.api_token');

        // 2. Extract the Bearer Token from the Authorization Header
        $providedToken = $request->bearerToken();

        // 4. Validate presence and matching accuracy
        if (!$providedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access. Invalid or missing API token.'
            ], 401);
        }

        return $next($request);
    }
}
