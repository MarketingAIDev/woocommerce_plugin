<?php

namespace Acelle\Http;

use Acelle\Http\Middleware\Backend;
use Acelle\Http\Middleware\EncryptCookies;
use Acelle\Http\Middleware\Frontend;
use Acelle\Http\Middleware\Installed;
use Acelle\Http\Middleware\NotInstalled;
use Acelle\Http\Middleware\NotLoggedIn;
use Acelle\Http\Middleware\RedirectIfAuthenticated;
use Acelle\Http\Middleware\SelectedCustomer;
use Acelle\Http\Middleware\Subscription;
use Acelle\Http\Middleware\VerifyCsrfToken;
use BeyondCode\ServerTiming\Middleware\ServerTimingMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            ServerTimingMiddleware::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],
        'web_nocsrf' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'frontend' => Frontend::class,
        'backend' => Backend::class,
        'installed' => Installed::class,
        'not_installed' => NotInstalled::class,
        'not_logged_in' => NotLoggedIn::class,
        'subscription' => Subscription::class,
        'selected_customer' => SelectedCustomer::class
    ];
}
