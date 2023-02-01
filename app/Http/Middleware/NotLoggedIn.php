<?php

namespace Acelle\Http\Middleware;

use BeyondCode\ServerTiming\Facades\ServerTiming;
use Closure;

class NotLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        ServerTiming::start('NotLoggedInMiddleware');
        $default_language = \Acelle\Model\Language::find(\Acelle\Model\Setting::get('default_language'));
        if (isset($_COOKIE['last_language_code'])) {
            $language_code = $_COOKIE['last_language_code'];
        } elseif (is_object($default_language)) {
            $language_code = $default_language->code;
        } else {
            $language_code = 'en';
        }
        
        // Language
        try {
            if ($language_code) {
                \App::setLocale($language_code);
                \Carbon\Carbon::setLocale($language_code);
            }
        } catch (\Exception $e) {
        }
        ServerTiming::stop('NotLoggedInMiddleware');
        return $next($request);
    }
}
