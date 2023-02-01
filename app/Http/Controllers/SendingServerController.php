<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class SendingServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->selected_customer->can('read', new \Acelle\Model\SendingServer())) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $items = \Acelle\Model\SendingServer::search($request);

        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers.index',
                'items' => $items,
            ]);
        }
        return view('sending_servers.index', [
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if (!$request->selected_customer->can('read', new \Acelle\Model\SendingServer())) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $items = \Acelle\Model\SendingServer::search($request)->paginate($request->per_page);

        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers._list',
                'items' => $items,
            ]);
        }
        return view('sending_servers._list', [
            'items' => $items,
        ]);
    }

    /**
     * Select sending server type.
     *
     * @return \Illuminate\Http\Response
     */
    public function select(Request $request)
    {
        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers.select',
            ]);
        }
        return view('sending_servers.select');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $server = new \Acelle\Model\SendingServer();
        $server->status = 'active';
        $server->uid = '0';
        $server->quota_value = '1000';
        $server->quota_base = '1';
        $server->quota_unit = 'hour';
        $server->type = $request->type;
        $server->fill($request->old());

        // authorize
        if (!$request->selected_customer->can('create', $server)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers.create',
                'server' => $server,
            ]);
        }
        return view('sending_servers.create', [
            'server' => $server,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get current user
        $current_user = $request->user();
        $server = new \Acelle\Model\SendingServer();
        $server->type = $request->type;

        // authorize
        if (!$request->selected_customer->can('create', $server)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            $this->validate($request, \Acelle\Model\SendingServer::frontendRules($request->type));

            // Save current user info
            $server->fill($request->all());
            $server->customer_id = $request->selected_customer->id;
            $server->status = 'active';

            // bounce / feedback hanlder nullable
            if (empty($request->bounce_handler_id)) {
                $server->bounce_handler_id = null;
            }
            if (empty($request->feedback_loop_handler_id)) {
                $server->feedback_loop_handler_id = null;
            }

            if ($server->save()) {
                // Log
                $server->log('created', $request->selected_customer);

                $request->session()->flash('alert-success', trans('messages.sending_server.created'));
                if($request->wantsJson()){
                    return response()->json([
                        'redirectAction' => 'SendingServerController@index',
                    ]);
                }
                return redirect()->action('SendingServerController@index');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $server = \Acelle\Model\SendingServer::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // bounce / feedback hanlder nullable
        if ($request->old() && empty($request->old()["bounce_handler_id"])) {
            $server->bounce_handler_id = null;
        }
        if ($request->old() && empty($request->old()["feedback_loop_handler_id"])) {
            $server->feedback_loop_handler_id = null;
        }

        $server->fill($request->old());

        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers.edit',
                'server' => $server,
            ]);
        }
        return view('sending_servers.edit', [
            'server' => $server,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Get current user
        $current_user = $request->user();
        $server = \Acelle\Model\SendingServer::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            // Save current user info
            $server->fill($request->all());

            // bounce / feedback hanlder nullable
            if (empty($request->bounce_handler_id)) {
                $server->bounce_handler_id = null;
            }

            if (empty($request->feedback_loop_handler_id)) {
                $server->feedback_loop_handler_id = null;
            }
            
            $this->validate($request, $server->getFrontendRules());

            if ($server->save()) {
                // Log
                $server->log('updated', $request->selected_customer);

                $request->session()->flash('alert-success', trans('messages.sending_server.updated'));
                if($request->wantsJson()){
                    return response()->json([
                        'redirectAction' => 'SendingServerController@index',
                        'server' => $server,
                    ]);
                }
                return redirect()->action('SendingServerController@index');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = \Acelle\Model\SendingServer::findByUid($row[0]);

            // authorize
            if (!$request->selected_customer->can('update', $item)) {
                if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->save();
        }

        echo trans('messages.sending_server.custom_order.updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items = \Acelle\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->selected_customer->can('delete', $item)) {
                // Log
                $item->log('deleted', $request->selected_customer);

                $item->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.deleted');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $items = \Acelle\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->selected_customer->can('disable', $item)) {
                $item->disable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.disabled');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
        $items = \Acelle\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->selected_customer->can('enable', $item)) {
                $item->enable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.enabled');
    }

    /**
     * Test Sending server.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request, $uid)
    {
        // Get current user
        $current_user = $request->user();

        // Fill new server info
        if ($uid) {
            $server = \Acelle\Model\SendingServer::findByUid($uid);
        } else {
            $server = new \Acelle\Model\SendingServer();
            $server->uid = 0;
        }

        $server->fill($request->all());
        $server->type = $request->type;

        // authorize
        if (!$request->selected_customer->can('test', $server)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // @todo testing method and return result here. Ex: echo json_encode($server->test())
            try {
                $server->sendTestEmail([
                    'from_email' => $request->from_email,
                    'to_email' => $request->to_email,
                    'subject' => $request->subject,
                    'plain' => $request->content
                ]);
            } catch (\Exception $ex) {
                echo json_encode([
                    'status' => 'error', // or success
                    'message' => $ex->getMessage()
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success', // or success
                'message' => trans('messages.sending_server.test_success')
            ]);
            return;
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'sending_servers.test',
                'server' => $server
            ]);
        }

        return view('sending_servers.test', [
            'server' => $server,
        ]);
    }
}