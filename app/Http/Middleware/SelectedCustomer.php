<?php

namespace Acelle\Http\Middleware;

use Acelle\Model\Customer;
use Acelle\Model\User;
use BeyondCode\ServerTiming\Facades\ServerTiming;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SelectedCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws ValidationException
     */
    public function handle($request, Closure $next)
    {
        ServerTiming::start('SelectedCustomerMiddleware');
        /** @var User $user */
        $user = $request->user();
        $selected_customer_uid = $request->header('X-Emailwish-Customer-UID');
        if(empty($selected_customer_uid))
            $selected_customer_uid = $request->cookie('selected_customer_uid');

        if($user && empty($selected_customer_uid)){

            //throw ValidationException::withMessages([
            //    'selected_customer_uid' => 'UID of a customer who belongs to the logged in user is required'
            //]);

            $customer = $user->customers()->first();
            if(!$customer){
                ServerTiming::stop('SelectedCustomerMiddleware');
                throw ValidationException::withMessages([
                    'selected_customer_uid' => 'UID of a customer who belongs to the logged in user is required'
                ]);
            }
            $request->selected_customer = $customer;
            ServerTiming::stop('SelectedCustomerMiddleware');
            return $next($request);
        }

        $customer = Customer::findByUid($selected_customer_uid);
        if (!$user || !$customer || $customer->user->id != $user->id) {
            ServerTiming::stop('SelectedCustomerMiddleware');
            throw ValidationException::withMessages([
                'selected_customer_uid' => 'UID of a customer who belongs to the logged in user is required'
            ]);
        }

        if(!$customer->shopify_shop) {
            ServerTiming::stop('SelectedCustomerMiddleware');
            throw ValidationException::withMessages([
                'selected_customer_uid' => 'Selected customer doesn\'t have a shop'
            ]);
        }

        $request->selected_customer = $customer;
        ServerTiming::stop('SelectedCustomerMiddleware');

        /** @var Response $response */
        $response = $next($request);
        if($response instanceof BinaryFileResponse) return $response;
        return $response->withCookie(cookie()->forever('selected_customer_uid', $customer->uid));
    }
}
