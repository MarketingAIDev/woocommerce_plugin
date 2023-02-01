<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
    }

    /**
     * Check if the site is in demo mode.
     *
     * @return \Illuminate\Http\Response
     */
    public function isDemoMode()
    {
        return config('app.demo');
    }

    /**
     * Check if the user is not authorized.
     *
     * @return \Illuminate\Http\Response
     */
    public function notAuthorized()
    {
        if (request()->ajax()) {
            return response()->json(['message' => trans('messages.not_authorized_message')], 403);
        }

        return response()->view('notAuthorized')->setStatusCode(403);
    }

    /**
     * Check if the user cannot create more item.
     *
     * @return \Illuminate\Http\Response
     */
    public function noMoreItem()
    {
        return view('noMoreItem');
    }

    /**
     * When site status is offline.
     *
     * @return \Illuminate\Http\Response
     */
    public function offline()
    {
        return view('offline');
    }

    /**
     * Show demo home page.
     *
     * @return \Illuminate\Http\Response
     */
    public function demo()
    {
        return view('demo');
    }

    /**
     * Go to demo admin/campaign page.
     *
     * @return \Illuminate\Http\Response
     */
    public function demoGo(Request $request)
    {
        \Auth::logout();

        if ($request->view == 'backend') {
            session()->put('demo', 'backend');
            return redirect()->action('Admin\HomeController@index');
        } else {
            session()->put('demo', 'frontend');
            return redirect()->action('HomeController@index');
        }
    }

    /**
     * Docs for api v1.
     *
     * @return \Illuminate\Http\Response
     */
    public function docsApiV1()
    {
        return view('docs.api.v1', ['view' => 'frontend']);
    }

    /**
     * Login from outsite.
     *
     * @return \Illuminate\Http\Response
     */
    public function autoLogin($api_token)
    {
        $user = \Acelle\Model\User::where('api_token', $api_token)->first();

        \Auth::login($user);

        return redirect()->action('HomeController@index');
    }

    /**
     * Translate datatable.
     *
     * @return text
     */
    public function datatable_locale()
    {
        echo '{
            "sEmptyTable":     "'.trans('messages.datatable_sEmptyTable').'",
            "sProcessing":   "'.trans('messages.datatable_sProcessing').'",
            "sLengthMenu":   "'.trans('messages.datatable_sLengthMenu').'",
            "sZeroRecords":  "'.trans('messages.datatable_sZeroRecords').'",
            "sInfo":         "'.trans('messages.datatable_sInfo').'",
            "sInfoEmpty":    "'.trans('messages.datatable_sInfoEmpty').'",
            "sInfoFiltered": "'.trans('messages.datatable_sInfoFiltered').'",
            "sInfoPostFix":  "'.trans('messages.datatable_sInfoPostFix').'",
            "sSearch":       "'.trans('messages.datatable_sSearch').'",
            "sUrl":          "'.trans('messages.datatable_sUrl').'",
            "oPaginate": {
                "sFirst":    "'.trans('messages.datatable_sFirst').'",
                "sPrevious": "'.trans('messages.datatable_sPrevious').'",
                "sNext":     "'.trans('messages.datatable_sNext').'",
                "sLast":     "'.trans('messages.datatable_sLast').'"
            },
            "oAria": {
                "sSortAscending":  "'.trans('messages.datatable_sSortAscending').'",
                "sSortDescending": "'.trans('messages.datatable_sSortDescending').'"
            }
        }';
    }

    /**
     * Translate jqery validate.
     *
     * @return text
     */
    public function jquery_validate_locale()
    {
        echo 'jQuery.extend(jQuery.validator.messages, {
                required: "'.trans('messages.jvalidate_required').'",
                remote: "'.trans('messages.jvalidate_remote').'",
                email: "'.trans('messages.jvalidate_email').'",
                url: "'.trans('messages.jvalidate_url').'",
                date: "'.trans('messages.jvalidate_date').'",
                dateISO: "'.trans('messages.jvalidate_dateISO').'",
                number: "'.trans('messages.jvalidate_number').'",
                digits: "'.trans('messages.jvalidate_digits').'",
                creditcard: "'.trans('messages.jvalidate_creditcard').'",
                equalTo: "'.trans('messages.jvalidate_equalTo').'",
                accept: "'.trans('messages.jvalidate_accept').'",
                maxlength: jQuery.validator.format("'.trans('messages.jvalidate_maxlength').'"),
                minlength: jQuery.validator.format("'.trans('messages.jvalidate_minlength').'"),
                rangelength: jQuery.validator.format("'.trans('messages.jvalidate_rangelength').'"),
                range: jQuery.validator.format("'.trans('messages.jvalidate_range').'"),
                max: jQuery.validator.format("'.trans('messages.jvalidate_max').'"),
                min: jQuery.validator.format("'.trans('messages.jvalidate_min').'")
            });';
    }

    /**
     * Reload application settings
     *
     * @todo only authenticated user can execute this
     */
    public function reloadCache()
    {
        $next = action('HomeController@index');
        \Artisan::call('config:cache');
        sleep(5);
        return redirect()->away($next);
    }

    /**
     * Reload application settings
     *
     * @todo only authenticated user can execute this
     */
    public function runMigration()
    {
        \Artisan::call('migrate', ['--force' => true]);
        \Acelle\Model\Notification::truncate();
        sleep(5);
        echo "Migration done! Redirecting...<meta http-equiv=\"refresh\" content=\"5;URL='".url('/')."'\" />";
    }

    /**
     * Run remote cronjob
     *
     * @return boolean
     */
    public function remoteJob($remote_job_token)
    {
        if (\Acelle\Model\Setting::get('remote_job_token') === $remote_job_token) {
            // @todo remote cronjob code here...
        } else {
            echo trans('messages.invalid_token');
        }
    }
    
    /**
     * When user is diabled.
     *
     * @return \Illuminate\Http\Response
     */
    public function userDisabled()
    {
        \Auth::logout();
        return view('isDisabled');
    }

    /**
     * Store the referrer from outside the app to redirect back.
     * This is required for the core may be accessed from outside, for example, by the React App.
     * The React app may take the user to a core URL, and the user may navigate within core,
     * but when the user clicks "Go back" or similar, we need the original React referrer address to take the user back.
     *
     * @param $controllerName string the name of the controller making this request. needed to keep URLs separate.
     */
    public function setReferrer($controllerName)
    {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referrer_url = parse_url($_SERVER['HTTP_REFERER']);
            if (!empty($referrer_url) && !empty($referrer_url['host']) && $referrer_url['host'] != $_SERVER['HTTP_HOST']) {
                session()->put("REFERRER.$controllerName", $_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function getReferrer($controllerName, $defaultValue = null)
    {
        return session()->get("REFERRER.$controllerName", $defaultValue);
    }

    public function selectOptionsWithLabel($options)
    {
        $results = [];
        foreach ($options as $option) {
            $label = "";
            if (!empty($option['text']))
                $label = $option['text'];
            if (!empty($option['title']))
                $label = $option['title'];
            $option['label'] = $label;
            $results[] = $option;
        }
        return $results;
    }

    public function appkey()
    {
        echo get_app_identity();
    }
}
