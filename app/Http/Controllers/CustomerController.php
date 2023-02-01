<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function listing(Request $request)
    {
        $request->merge(["user_id" => $request->user()->id]);

        $customers = Customer::paged_search($request);
        return response()->json([
            'customers' => $customers,
        ]);
    }

    /**
     * Render customer image.
     */
    public function avatar(Request $request)
    {
        // Get current customer
        if ($request->uid != '0') {
            $customer = \Acelle\Model\Customer::findByUid($request->uid);
        } else {
            $customer = new \Acelle\Model\Customer();
        }
        if (!empty($customer->imagePath())) {
            try {
                $img = \Image::make($customer->imagePath());
            } catch (\Intervention\Image\Exception\NotReadableException $ex) {
                // file not found
                $customer->image = null;
                $customer->save();
                $img = \Image::make(public_path('assets/images/placeholder.jpg'));
            }
        } else {
            $img = \Image::make(public_path('assets/images/placeholder.jpg'));
        }

        return $img->response();
    }

    /**
     * User uid for editor
     */
    public function showUid(Request $request)
    {
        $user = $request->user();
        echo $user->uid;
    }

    /**
     * Log in back user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loginBack(Request $request)
    {
        $id = \Session::pull('orig_customer_id');
        $orig_user = \Acelle\Model\User::findByUid($id);

        \Auth::login($orig_user);

        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'Admin\CustomerController@index'
            ]);
        }
        return redirect()->action('Admin\CustomerController@index');
    }

    /**
     * Admin area.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function adminArea(Request $request)
    {/*
        $id = \Session::get('orig_customer_id');
        $orig_user = \Acelle\Model\User::findByUid($id);
        $gateway = Cashier::getPaymentGateway();

        // Get current subscription
        $customer = $request->selected_customer;
        $subscription = $customer->subscription;

        $next_billing_date = null;
        if (is_object($subscription)) {
            $next_billing_date = $subscription->ends_at;
            if ($subscription->isActive()) {
                $next_billing_date = $subscription->current_period_ends_at;
            }
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'customers.admin_area',
                'customer' => $customer,
                'subscription' => $subscription,
                'next_billing_date' => $next_billing_date,
                'gateway' => $gateway
            ]);
        }
        return view('customers.admin_area', [
            'customer' => $customer,
            'subscription' => $subscription,
            'next_billing_date' => $next_billing_date,
            'gateway' => $gateway,
        ]);*/
    }
}
