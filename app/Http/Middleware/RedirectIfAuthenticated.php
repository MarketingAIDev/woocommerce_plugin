<?php

namespace Acelle\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectURL' => "/",
                    'errors' => ["Already logged in!"]
                ], 401);
            }
            return redirect('/');
        }

        return $next($request);
    }
}
