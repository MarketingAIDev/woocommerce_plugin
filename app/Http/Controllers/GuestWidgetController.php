<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;

class GuestWidgetController extends Controller
{
    function widgets_status(Request $request)
    {
        $customer = Customer::findByUid($request->input('client_uid'));
        if (!$customer)
            return response("", 404);

        return response()->json([
            'chat_module_enabled' => $customer->shopify_shop->enable_chat_script,
            'review_module_enabled' => $customer->shopify_shop->enable_review_script,
            'popup_module_enabled' => $customer->shopify_shop->enable_popup_script,
            'current_version' => '0.60'
        ]);
    }
}