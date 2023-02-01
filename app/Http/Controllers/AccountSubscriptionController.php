<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Plan;
use Acelle\Model\Setting;
use Illuminate\Http\Request;

class AccountSubscriptionController extends Controller
{
    /**
     * Customer subscription main page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function index(Request $request)
    {
        /*// Check if system dosen't have payment gateway
        if (!Setting::get('system.payment_gateway')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'noPrimaryPayment'
                ]);
            }
            return view('noPrimaryPayment');
        }

        $customer = $request->selected_customer;
        //$gateway = Cashier::getPaymentGateway();

        // Get current subscription
        $subscription = $customer->subscription;

        // Customer dose not have subscription
        if (!is_object($subscription) || $subscription->isEnded()) {
            $plans = Plan::getAvailablePlans();
            $planCount = Plan::getAllActive()->count();
            $colWidth = ($planCount == 0) ? 0 : round(85 / $planCount);
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'account.subscription.select_plan',
                    'plans' => $plans,
                    'colWidth' => $colWidth,
                    'subscription' => $subscription,
                ]);
            }
            return view('account.subscription.select_plan', [
                'plans' => $plans,
                'colWidth' => $colWidth,
                'subscription' => $subscription,
            ]);
        }

        if (!$subscription->plan->isActive()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'account.subscription_error',
                    'message' => __('messages.subscription.error.plan-not-active', [ 'name' => $subscription->plan->name])
                ]);
            }
            return view('account.subscription.error', ['message' => __('messages.subscription.error.plan-not-active', ['name' => $subscription->plan->name])]);
        }

        // Check if subscription is new
        if ($subscription->isNew()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectURL' => $gateway->getCheckoutUrl($subscription, action('AccountSubscriptionController@index'))
                ]);
            }
            return redirect()->away($gateway->getCheckoutUrl($subscription, action('AccountSubscriptionController@index')));
        }

        // Check if subscription is new
        if ($subscription->isPending()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectURL' => $gateway->getPendingUrl($subscription, action('AccountSubscriptionController@index'))
                ]);
            }
            return redirect()->away($gateway->getPendingUrl($subscription, action('AccountSubscriptionController@index')));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'account.subscription.index',
                'subscription' => $subscription,
                'gateway' => $gateway,
                'plan' => $subscription->plan,
            ]);
        }
        return view('account.subscription.index', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plan' => $subscription->plan,
        ]);*/
    }

    /**
     * Store customer subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function create(Request $request)
    {
        /*// Get current customer
        $customer = $request->selected_customer;
        $plan = Plan::findByUid($request->plan_uid);
        $gateway = Cashier::getPaymentGateway();

        // Create subscription
        $subscription = $gateway->create($customer, $plan);

        try {
            \Mail::to($customer->user->email)->send(new \Acelle\Mail\SubscriptionDoneMailer($subscription));
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Can not send email: ' . $e->getMessage());
        }

        // Check if subscriotion is new
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'AccountSubscriptionController@index'
            ]);
        }
        return redirect()->action('AccountSubscriptionController@index');*/
    }

    /**
     * Review customer subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function review(Request $request)
    {
        /*// Get current customer
        $customer = $request->selected_customer;
        $subscription = $customer->subscription;
        $plan = Plan::findByUid($request->plan_uid);

        if (isset($subscription) && !$subscription->isEnded()) {
            $request->session()->flash('alert-error', 'Already subscribed a plan!');
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => "AccountSubscriptionController@index"
                ]);
            }
            return redirect()->action('AccountSubscriptionController@index');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'account.subscription.review',
                'plan' => $plan,
            ]);
        }
        return view('account.subscription.review', [
            'plan' => $plan,
        ]);*/
    }

    /**
     * Change plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function changePlan(Request $request)
    {
        /*$customer = $request->selected_customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();
        $plans = Plan::getAvailablePlans();

        // Authorization
        if (!$request->selected_customer->can('changePlan', $subscription)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => "account.subscription.change_plan",
                'subscription' => $subscription,
                'gateway' => $gateway,
                'plans' => $plans,
            ]);
        }
        return view('account.subscription.change_plan', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plans' => $plans,
        ]);*/
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        /*$customer = $request->selected_customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();

        if ($request->selected_customer->can('cancel', $subscription)) {
            $gateway->cancel($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled'));
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'AccountSubscriptionController@index'
            ]);
        }
        return redirect()->action('AccountSubscriptionController@index');*/
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resume(Request $request)
    {
        /*$customer = $request->selected_customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();

        if ($request->selected_customer->can('resume', $subscription)) {
            $gateway->resume($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.resumed'));
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'AccountSubscriptionController@index'
            ]);
        }
        return redirect()->action('AccountSubscriptionController@index');*/
    }

    /**
     * Cancel now subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelNow(Request $request)
    {
        /*
        $customer = $request->selected_customer;
        // Get current subscription
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();

        if ($request->selected_customer->can('cancelNow', $subscription)) {
            $gateway->cancelNow($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled_now'));
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'AccountSubscriptionController@index'
            ]);
        }
        return redirect()->action('AccountSubscriptionController@index');*/
    }
}
