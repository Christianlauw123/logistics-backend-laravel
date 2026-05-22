<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseAccessTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->bearerToken() && $request->cookie('access_token')) {
            $request->headers->set(
                'Authorization',
                'Bearer ' . $request->cookie('access_token')
            );
        }

        return $next($request);
    }
}
