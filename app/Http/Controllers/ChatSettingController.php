<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\ChatSession;
use Acelle\Model\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatSettingController extends Controller
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

        $settings = $customer->getChatSettings();

        if (!empty($settings['bot_image']))
            $settings['bot_image'] = url($settings['bot_image']);

        return response()->json(['chat_settings' => $settings]);
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

        $settings = $customer->getChatSettings();
        // Update
        $extensions = $settings['file_sharing_extensions'] ?? "";
        $extensions_array = explode(',', $extensions);
        $settings['file_sharing_extensions'] = $extensions_array;

        // Branding
        $branding_allowed = $customer->getActiveShopifyRecurringApplicationCharge()->plan->getOption('custom_branding');
        $settings['custom_branding'] = $branding_allowed && $branding_allowed == "yes";

        // Bot image
        if (!empty($settings['bot_image']))
            $settings['bot_image'] = url($settings['bot_image']);

        return response()->json(['chat_settings' => $settings]);
    }

    public function store(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $customer->validateAndUpdateChatSettings($request->all());

        return response()->json([
            'message' => "Chat settings updated"
        ]);
    }
}
