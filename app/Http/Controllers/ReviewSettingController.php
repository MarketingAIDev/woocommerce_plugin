<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer')->except('readGuest');
        parent::__construct();
    }

    public function index(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;

        $settings = $customer->getReviewSettings();

        return response()->json(['review_settings' => $settings]);
    }

    public function readGuest(Request $request)
    {
        $data = $request->validate([
            'client_uid' => 'required|string'
        ]);

        $customer = Customer::findByUid($data['client_uid']);
        if (!$customer) {
            throw ValidationException::withMessages([
                'client_uid' => 'Invalid id'
            ]);
        }

        $settings = $customer->getReviewSettings();

        return response()->json(['review_settings' => $settings]);
    }

    public function store(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $customer->validateAndUpdateReviewSettings($request->all());

        return response()->json([
            'message' => "Review settings updated"
        ]);
    }
}
