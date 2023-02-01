<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\Popup;
use Acelle\Model\PopupImpression;
use Acelle\Model\PopupResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class PopupGuestController extends Controller
{

    public function mark_shown(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|exists:popups,uid'
        ]);
        $popup = Popup::findByUid($data['uid']);

        Cookie::queue('popup_' . $data['uid'], 'shown');
        PopupImpression::storeImpression($popup);
        return response("", 200);
    }

    public function submit(Request $request)
    {
        $data = $request->validate(PopupResponse::store_response_rules());
        $popup = Popup::findByUid($data['uid'] ?? '');

        PopupResponse::store_response($popup, $data);

        Cookie::queue('popup_' . $data['uid'], 'submitted');
        return response("", 200);
    }

    function view(Request $request)
    {
        $data = $request->validate(['uid' => 'required|string|exists:popups,uid']);
        $popup = Popup::findByUid($data['uid']);

        return response()->json([
            'popup' => $popup
        ]);
    }


    function index(Request $request)
    {
        $data = $request->validate(['client_uid' => 'required|string|exists:customers,uid']);
        $customer = Customer::findByUid($data['client_uid']);

        $active_popups = $customer->active_popups;
        $new_popups = [];
        foreach ($active_popups as $popup) {
            if (Cookie::get('popup_' . $popup->uid) == "submitted")
                continue;
            if (Cookie::get('popup_' . $popup->uid) == "shown" && $popup->behaviour == Popup::BEHAVIOUR_once)
                continue;
            $new_popups[] = $popup;
        }
        return response()->json([
            "popups" => $new_popups
        ]);
    }
}
