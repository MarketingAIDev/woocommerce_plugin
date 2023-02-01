<?php

namespace Acelle\Http\Middleware;

use BeyondCode\ServerTiming\Facades\ServerTiming;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'broadcasting/auth',
        'users/update_user_activity',
        'message/send_message',
        '_shopify/*',
        'ew_dynamic/*',
        '_builder/*',
        '_builder_demo/*',
        'delivery/*',
        'api/*',
        '*/embedded-form-*',
        'payments/stripe/credit-card*',
        'payments/paddle/card*/hook',
        'payments/payumoney-success/*',
        'payments/payumoney-fail/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    "errors" => [
                        "csrf" => ["CSRF Token verification failed!"]
                    ]
                ], 419);
            }
            throw $e;
        }
    }
}
