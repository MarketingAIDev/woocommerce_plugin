<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\SocialMediaLink;
use Acelle\Model\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SocialMediaLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function index(Request $request)
    {
        $customer = $request->selected_customer;
        return response()->json(['social_media_links' => $customer->social_media_links]);
    }

    public function store(Request $request)
    {
        $customer = $request->selected_customer;

        $data = $this->validate($request, SocialMediaLink::createRules());
        return $customer->social_media_links()->create($data);
    }

    public function update(Request $request)
    {
        $link = SocialMediaLink::findByUid($request->uid);
        if (!$link || $link->customer_id != $request->selected_customer_id) {
            $exception = ValidationException::withMessages([
                'uid' => 'entry not found'
            ]);
            throw  $exception;
        }
        $data = $this->validate($request, SocialMediaLink::updateRules());

        return $link->update($data);
    }

    public function delete(Request $request)
    {
        $items = SocialMediaLink::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            $item->delete();
            //            // authorize
            //            if ($request->selected_customer->can('delete', $item)) {
            //                // Log
            //                $item->log('deleted', $request->selected_customer);
            //
            //                $item->delete();
            //            }
        }

        // Redirect to my lists page
        echo 'deleted';
        //echo trans('messages.sending_domains.deleted');
    }
}
