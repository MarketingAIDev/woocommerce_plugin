<?php

namespace Acelle\Http\Middleware;

use Closure;
use Acelle\Model\User;

class Frontend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        return $next($request);

        $user = $request->user();
       
        // If user have no frontend access but has backend access
        if (isset($user) && !$user->can("customer_access", User::class) && $user->can("admin_access", User::class)) {
            return redirect()->action('Admin\HomeController@index');
        }
        
        // check if user not authorized for customer access
        if (!$user->can("customer_access", User::class)) {
            return redirect()->action('Controller@notAuthorized');
        }

        // Site offline
        if (\Acelle\Model\Setting::get('site_online') == 'false' &&
            (isset($user) && $request->selected_customer->getOption('access_when_offline') != 'yes')
        ) {
            return redirect()->action('Controller@offline');
        }
        
        // If user is disabled
        if (
            (isset($user) && is_object($request->selected_customer) && !$request->selected_customer->isActive())
        ) {
            return redirect()->action('Controller@userDisabled');
        }

        // Language
//        if (is_object($request->selected_customer->language)) {
//            \App::setLocale($request->selected_customer->language->code);
//            \Carbon\Carbon::setLocale($request->selected_customer->language->code);
//        }

        return $next($request);
    }
}
