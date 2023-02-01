<?php

namespace Acelle\Http\Controllers;

use Acelle\Jobs\CreateDiscountCodes;
use Acelle\Model\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefaultAutomationsController extends Controller
{
    const DISCOUNT_COUPON_abandoned_cart_10 = 'abandoned_cart_10';
    const DISCOUNT_COUPON_abandoned_cart_20 = 'abandoned_cart_20';
    const DISCOUNT_COUPON_popup_discount_20 = 'popup_discount_20';
    // const DISCOUNT_COUPON_apology = 'apology';
    const DISCOUNT_COUPON_review_with_no_image_10 = 'review_with_no_image_10';
    const DISCOUNT_COUPON_review_with_image_20 = 'review_with_image_20';

    const DEFAULT_DISCOUNT_COUPONS = [
        self::DISCOUNT_COUPON_abandoned_cart_10 => [
            'id' => self::DISCOUNT_COUPON_abandoned_cart_10,
            'code' => 'EWACart',
            'label' => 'AbandonedCart First Reminder Discount Percentage',
            'discount' => '10'
        ],
        self::DISCOUNT_COUPON_abandoned_cart_20 => [
            'id' => self::DISCOUNT_COUPON_abandoned_cart_20,
            'code' => 'EWACart2',
            'label' => 'AbandonedCart Second Reminder Discount Percentage',
            'discount' => '20'
        ],
        self::DISCOUNT_COUPON_popup_discount_20 => [
            'id' => self::DISCOUNT_COUPON_popup_discount_20,
            'code' => 'EWPop',
            'label' => 'Popup Discount Percentage',
            'discount' => '20'
        ],
        /*self::DISCOUNT_COUPON_apology => [
            'id' => self::DISCOUNT_COUPON_apology,
            'code' => 'EWApology',
            'label' => 'Negative Review Discount Percentage',
            'discount' => '20'
        ],*/
        self::DISCOUNT_COUPON_review_with_no_image_10 => [
            'id' => self::DISCOUNT_COUPON_review_with_no_image_10,
            'code' => 'EWRevWNImg',
            'label' => 'Discount Percentage for Review with no image',
            'discount' => '10'
        ],
        self::DISCOUNT_COUPON_review_with_image_20 => [
            'id' => self::DISCOUNT_COUPON_review_with_image_20,
            'code' => 'EWRevWImg',
            'label' => 'Discount Percentage for Review with image',
            'discount' => '20'
        ],
    ];

    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    function discount_coupons(): JsonResponse
    {
        return response()->json([
            'default_discounts' => self::DEFAULT_DISCOUNT_COUPONS
        ]);
    }

    function skip_automations(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;

        $shop->default_automations_created = 1;
        $shop->save();
        return response()->json(['message' => 'OK']);
    }

    function create_automations(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;

        $data = custom_validate($request->all(), [
            'use_defaults' => 'required|boolean'
        ]);
        $use_defaults = $data['use_defaults'] ?? false;

        $discount_data = self::DEFAULT_DISCOUNT_COUPONS;
        if (!$use_defaults) {
            $data = custom_validate($request->all(), [
                self::DISCOUNT_COUPON_abandoned_cart_10 => 'required|array',
                self::DISCOUNT_COUPON_abandoned_cart_20 => 'required|array',
                self::DISCOUNT_COUPON_popup_discount_20 => 'required|array',
//                self::DISCOUNT_COUPON_apology => 'required|array',
                self::DISCOUNT_COUPON_review_with_no_image_10 => 'required|array',
                self::DISCOUNT_COUPON_review_with_image_20 => 'required|array',
                self::DISCOUNT_COUPON_abandoned_cart_10 . '.code' => 'required|string|min:5|max:100',
                self::DISCOUNT_COUPON_abandoned_cart_20 . '.code' => 'required|string|min:5|max:100',
                self::DISCOUNT_COUPON_popup_discount_20 . '.code' => 'required|string|min:5|max:100',
//                self::DISCOUNT_COUPON_apology . '.code' => 'required|string|min:5|max:100',
                self::DISCOUNT_COUPON_review_with_no_image_10 . '.code' => 'required|string|min:5|max:100',
                self::DISCOUNT_COUPON_review_with_image_20 . '.code' => 'required|string|min:5|max:100',
                self::DISCOUNT_COUPON_abandoned_cart_10 . '.discount' => 'required|numeric|min:0|max:100',
                self::DISCOUNT_COUPON_abandoned_cart_20 . '.discount' => 'required|numeric|min:0|max:100',
                self::DISCOUNT_COUPON_popup_discount_20 . '.discount' => 'required|numeric|min:0|max:100',
//                self::DISCOUNT_COUPON_apology . '.discount' => 'required|numeric|min:0|max:100',
                self::DISCOUNT_COUPON_review_with_no_image_10 . '.discount' => 'required|numeric|min:0|max:100',
                self::DISCOUNT_COUPON_review_with_image_20 . '.discount' => 'required|numeric|min:0|max:100',
            ]);
            $discount_data[self::DISCOUNT_COUPON_abandoned_cart_10]['code'] = $data[self::DISCOUNT_COUPON_abandoned_cart_10]['code'];
            $discount_data[self::DISCOUNT_COUPON_abandoned_cart_20]['code'] = $data[self::DISCOUNT_COUPON_abandoned_cart_20]['code'];
            $discount_data[self::DISCOUNT_COUPON_popup_discount_20]['code'] = $data[self::DISCOUNT_COUPON_popup_discount_20]['code'];
            $discount_data[self::DISCOUNT_COUPON_review_with_no_image_10]['code'] = $data[self::DISCOUNT_COUPON_review_with_no_image_10]['code'];
            $discount_data[self::DISCOUNT_COUPON_review_with_image_20]['code'] = $data[self::DISCOUNT_COUPON_review_with_image_20]['code'];
            $discount_data[self::DISCOUNT_COUPON_abandoned_cart_10]['discount'] = $data[self::DISCOUNT_COUPON_abandoned_cart_10]['discount'];
            $discount_data[self::DISCOUNT_COUPON_abandoned_cart_20]['discount'] = $data[self::DISCOUNT_COUPON_abandoned_cart_20]['discount'];
            $discount_data[self::DISCOUNT_COUPON_popup_discount_20]['discount'] = $data[self::DISCOUNT_COUPON_popup_discount_20]['discount'];
            $discount_data[self::DISCOUNT_COUPON_review_with_no_image_10]['discount'] = $data[self::DISCOUNT_COUPON_review_with_no_image_10]['discount'];
            $discount_data[self::DISCOUNT_COUPON_review_with_image_20]['discount'] = $data[self::DISCOUNT_COUPON_review_with_image_20]['discount'];
        }

        dispatch(new CreateDiscountCodes($shop, $discount_data));

        $shop->default_automations_created = 1;
        $shop->save();
        return response()->json(['message' => 'OK']);
    }

}