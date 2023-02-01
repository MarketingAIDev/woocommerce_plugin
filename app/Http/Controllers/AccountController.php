<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{

    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    /**
     * Update user profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     **/
    public function profile(Request $request)
    {
        // Get current user
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $customer->getColorScheme();

        // Authorize
        if (!$request->selected_customer->can('profile', $customer)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Save posted data
        if ($request->isMethod('post')) {
            // Prenvent save from demo mod
            if ($this->isDemoMode()) {
                if($request->wantsJson()){
                    return response()->json([
                        'view' => 'notAuthorized',
                    ]);
                }
                return $this->notAuthorized();
            }

            $this->validate($request, $customer->rules());

            // Update user account for customer
            $user = $customer->user;
            $user->email = $request->email;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->timezone = $request->timezone;
            // Update password
            if (!empty($request->password)) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            // Save current user info
            $customer->fill($request->all());

            // Upload and save image
            if ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    // Remove old images
                    $customer->removeImage();
                    $customer->image = $customer->uploadImage($request->file('image'));
                    $customer->touch();
                    $user->touch();
                }
            }

            // Remove image
            if ($request->_remove_image == 'true') {
                $customer->removeImage();
                $customer->image = '';
            }

            if ($customer->save()) {
                $request->session()->flash('alert-success', trans('messages.profile.updated'));
            }
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'account.profile',
                    'customer' => $customer,
                    'message' => 'Profile has been updated'
                ]);
            }
        }

        if (!empty($request->old())) {
            $customer->fill($request->old());
            // User info
            $customer->user->fill($request->old());
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.profile',
                'customer' => $customer,
            ]);
        }
        return view('account.profile', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update customer contact information.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     **/
    public function contact(Request $request)
    {
        // Get current user
        $customer = $request->selected_customer;
        $contact = $customer->getContact();

        // Create new company if null
        if (!is_object($contact)) {
            $contact = new \Acelle\Model\Contact();
        }

        // save posted data
        if ($request->isMethod('post')) {
            // Prevent save contact
            if (isset($contact->id) && $this->isDemoMode()) {
                if($request->wantsJson()){
                    return response()->json([
                        'view' => 'notAuthorized',
                    ]);
                }
                return $this->notAuthorized();
            }

            $this->validate($request, \Acelle\Model\Contact::$rules);

            $contact->fill($request->all());

            // Save current user info
            if ($contact->save()) {
                if (is_object($contact)) {
                    $customer->contact_id = $contact->id;
                    $customer->save();
                }
                $request->session()->flash('alert-success', trans('messages.customer_contact.updated'));
            }
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.contact',
                'customer' => $customer,
                'contact' => $contact->fill($request->old()),
            ]);
        }

        return view('account.contact', [
            'customer' => $customer,
            'contact' => $contact->fill($request->old()),
        ]);
    }

    /**
     * User logs.
     */
    public function logs(Request $request)
    {
        $logs = $request->selected_customer->logs;

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.logs',
                'logs' => $logs,
            ]);
        }

        return view('account.logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Logs list.
     */
    public function logsListing(Request $request)
    {
        $logs = \Acelle\Model\Log::search($request)->paginate($request->per_page);

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.logs_listing',
                'logs' => $logs,
            ]);
        }

        return view('account.logs_listing', [
            'logs' => $logs,
        ]);
    }

    /**
     * Quta logs.
     */
    public function quotaLog(Request $request)
    {
        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.quota_log',
            ]);
        }
        return view('account.quota_log');
    }

    /**
     * Quta logs 2.
     */
    public function quotaLog2(Request $request)
    {
        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.quota_log_2',
            ]);
        }
        return view('account.quota_log_2');
    }

    /**
     * Api token.
     */
    public function api(Request $request)
    {
        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.api',
            ]);
        }
        return view('account.api');
    }

    /**
     * Subscription
     */
    public function subscription(Request $request)
    {
        $customer = $request->selected_customer;

        // Get current subscription
        $subscription = $customer->getCurrentSubscriptionIgnoreStatus();
        
        // Get next subscription
        $nextSubscription = $customer->getNextSubscription();

        // If has no subscription
        if (!$subscription) {
            if($request->wantsJson()){
                return response()->json([
                    'redirectAction' => 'AccountController@subscriptionNew',
                ]);
            }
            return redirect()->action('AccountController@subscriptionNew');
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.subscription',
                'subscription' => $subscription,
                'nextSubscription' => $nextSubscription,
            ]);
        }
        return view('account.subscription', [
            'subscription' => $subscription,
            'nextSubscription' => $nextSubscription,
        ]);
    }

    /**
     * Api token.
     */
    public function subscriptionNew(Request $request)
    {
        $customer = $request->selected_customer;

        $subscription = new \Acelle\Model\Subscription();
        $subscription->customer_id = $customer->id;

        if ($request->plan_uid) {
            $subscription->fillAttributes($request->all());
        }

        // authorize
        if (!$request->selected_customer->can('create', $subscription)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if (!empty($request->old())) {
            $subscription->fillAttributes($request->old());
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'account.subscription_new',
                'subscription' => $subscription,
            ]);
        }

        return view('account.subscription_new', [
            'subscription' => $subscription
        ]);
    }

    /**
     * Renew api token.
     */
    public function renewToken(Request $request)
    {
        $user = $request->user();

        $user->api_token = Str::random(60);
        $user->save();

        // Redirect to my lists page
        $request->session()->flash('alert-success', trans('messages.user_api.renewed'));

        if($request->wantsJson()){
            return response()->json([
                'redirectAction' => 'AccountController@api'
            ]);
        }

        return redirect()->action('AccountController@api');
    }
}
