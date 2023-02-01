<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Signature;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function index(Request $request)
    {
        $customer = $request->selected_customer;
        return response()->json(['signature' => $customer->signature]);
    }

    public function store(Request $request)
    {
        $customer = $request->selected_customer;

        $signature = Signature::validateAndSave($customer, $request->all());
        return response()->json(['signature' => $signature, 'message' => "Signature saved successfully!"]);
    }
}
