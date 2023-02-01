<?php

namespace Acelle\Http\Controllers\Auth;

use Acelle\Http\Controllers\Controller;
use Acelle\Library\Tool;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Acelle\Model\Setting;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required',
            'password' => 'required'
        ];

        if (Setting::isYes('login_recaptcha') && !Setting::isYes('theme.beta')) {
            if (!Tool::checkReCaptcha($request)) {
                $rules['recaptcha_invalid'] = 'required';
            }
        }

        $this->validate($request, $rules);
    }

    /**
     * @param Request $request
     * @param $user
     * @return mixed
     */
    public function authenticated(Request $request, $user)
    {
        // If user is not activated
        if (!$user->activated) {
            $uid = $user->uid;
            auth()->logout();

            if ($request->wantsJson()) {
                return response()->json([
                    'view' => "notActivated",
                    'uid' => $uid
                ]);
            }

            return view('notActivated', ['uid' => $uid]);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'redirectURL' => '/',
                'authenticated' => true
            ]);
        }

        return redirect()->intended('/');
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();

        if ($request->wantsJson()) {
            return response()->json([
                'redirectURL' => '/login'
            ]);
        }

        return redirect('/login');
    }

    /**
     * Get the failed login response instance.
     *
     * @param Request $request
     * @return mixed
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('auth.failed')];

        if ($request->wantsJson()) {
            return response()->json([
                'redirectURL' => "/login",
                'errors' => $errors
            ], 401);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @return mixed
     */
    protected function sendLoginResponse(Request $request)
    {

        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = $this->guard()->user();

        $auth_resp = $this->authenticated($request, $user);

        if ($auth_resp) {
            return $auth_resp;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'redirectURL' => $this->redirectPath(),
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

}
