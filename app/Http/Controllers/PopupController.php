<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\Popup;
use Acelle\Model\PopupResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PopupController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }
    public function index(Request $request)
    {
        //        if (!$request->selected_customer->can('read', new ShopifyShop())) {
        //            if ($request->wantsJson()) {
        //                return response()->json([
        //                    'view' => 'notAuthorized',
        //                ]);
        //            }
        //            return $this->notAuthorized();
        //        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $items = Popup::search($request);

        return response()->json([
            'view' => 'popups.index',
            'items' => $items,
        ]);
    }

    public function listing(Request $request)
    {
        //        if (!$request->selected_customer->can('read', new ShopifyShop())) {
        //            if ($request->wantsJson()) {
        //                return response()->json([
        //                    'view' => 'notAuthorized',
        //                ]);
        //            }
        //            return $this->notAuthorized();
        //        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $items = Popup::search($request)->paginate($request->per_page);

        return response()->json([
            'view' => 'popups._list',
            'items' => $items,
        ]);
    }

    public function search(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        /** @var Popup[] $items */
        $items = Popup::search($request)->limit(10)->get();

        $results = [];
        foreach ($items as $item) {
            $results[] = [
                "label" => $item->title,
                "value" => $item->id
            ];
        }

        return response()->json(["results" => $results]);
    }

    public function store(Request $request)
    {
        $popup = Popup::validateAndStorePopup($request->selected_customer, $request->all());

        return response()->json([
            'redirectAction' => 'PopupsController@show',
            'uid' => $popup->uid,
        ]);
    }

    public function show($id)
    {
        $popup = Popup::findByUid($id);

        return response()->json([
            'view' => 'popups.show',
            'popup' => $popup,
        ]);
    }

    function thumbnail($id)
    {
        $popup = Popup::findByUid($id);
        if(!$popup) return response("", 404);
        $headers = [
            'Access-Control-Allow-Origin' => '*'
        ];
        return response()->file(storage_path('app/' . $popup->thumbnail_file_path), $headers);
    }

    public function responses(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;

        $query = $customer->popup_responses()->orderBy('id', 'desc');

        $selected_popups = [];
        if(!empty($request->popup_ids))
        {
            if(is_array($request->popup_ids))
                $popup_ids = $request->popup_ids;
            else
                $popup_ids = explode(',', $request->popup_ids);

            if(count($popup_ids))
            {
                $query->whereIn(PopupResponse::COLUMN_popup_id, $popup_ids);
                /** @var Popup[] $popups */
                $popups = Popup::whereIn('id', $popup_ids)->get();
                foreach ($popups as $popup){
                    $selected_popups[] = [
                        'label' => $popup->title,
                        'value' => $popup->id
                    ];
                }
            }
        }

        return response()->json([
            'items' => $query->paginate($request->per_page ?? 20),
            'selected_popups' => $selected_popups
        ]);
    }

    public function update(Request $request, $id)
    {
        /** @var Popup $popup */
        $popup = Popup::findByUid($id);
        if (!$popup || $popup->customer_id != $request->selected_customer->id) {
            return response()->json([
                'error' => 'Not Found'
            ], 404);
        }

        $popup->validateAndUpdatePopup($request->all());

        return response()->json([
            'redirectAction' => 'PopupsController@show',
            'uid' => $popup->uid,
            'message' => "Popup has been updated"
        ]);
    }

    public function update_trigger(Request $request, $id)
    {
        /** @var Popup $popup */
        $popup = Popup::findByUid($id);
        if (!$popup || $popup->customer_id != $request->selected_customer->id) {
            return response()->json([
                'error' => 'Not Found'
            ], 404);
        }

        $data = $request->validate($popup->updateTriggerRules());

        $popup->update_popup_trigger($data);

        return response()->json([
            'redirectAction' => 'PopupsController@show',
            'uid' => $popup->uid,
        ]);
    }

    public function delete(Request $request)
    {
        $items = Popup::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            $item->delete();
            // // authorize
            //            if ($request->selected_customer->can('delete', $item)) {
            //                // Log
            //                $item->log('deleted', $request->selected_customer);
            //
            //                $item->delete();
            //            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_domains.deleted');
    }

    public function copy(Request $request, $uid)
    {
        $popup = Popup::findByUid($uid);
        // authorize
        if (Gate::denies('copy', $popup)) {
            return $this->notAuthorized();
        }
        $popup->makeCopy($request->get('copy_popup_name', $popup->title), $request->selected_customer);
        return response('Copied');
    }

    function public_popups(Request $request): JsonResponse
    {
        $popups = Popup::query()->where(Popup::COLUMN_default_for_new_customers, 1)->paginate($request->per_page);
        return response()->json([
            'public_popups' => $popups
        ]);
    }

    function update_public_flag(Request $request, $uid)
    {
        $popup = Popup::findByUid($uid);
        if (Gate::denies('update', $popup))
            return $this->notAuthorized();

        $user = $request->user();
        if (!$user || !($user->admin))
            return $this->notAuthorized();

        $data = custom_validate($request->all(), [
            Popup::COLUMN_default_for_new_customers => 'required|bool'
        ]);
        $popup->default_for_new_customers = (bool)($data[Popup::COLUMN_default_for_new_customers] ?? false);
        $popup->save();
        return response('Updated');
    }
}
