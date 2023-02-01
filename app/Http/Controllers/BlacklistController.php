<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class BlacklistController extends Controller
{

    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    /**
     * Search items.
     */
    public function search($request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $blacklists = \Acelle\Model\Blacklist::search($request);

        return $blacklists;
    }

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $customer = $request->selected_customer;

        if (!$customer->can('read', new \Acelle\Model\Blacklist())) {
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $blacklists = $this->search($request);

        # Get current job
        $system_job = $customer->getLastActiveImportBlacklistJob();
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists.index',
                'blacklists' => $blacklists,
                'system_job' => $system_job,
            ]);
        }
        return view('blacklists.index', [
            'blacklists' => $blacklists,
            'system_job' => $system_job,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function listing(Request $request)
    {
        if (!$request->selected_customer->can('read', new \Acelle\Model\Blacklist())) {
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        $blacklists = $this->search($request)->paginate($request->per_page);
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists._list',
                'blacklists' => $blacklists,
            ]);
        }
        return view('blacklists._list', [
            'blacklists' => $blacklists,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create(Request $request)
    {
        $blacklist = new \Acelle\Model\Blacklist([
            'signing_enabled' => true,
        ]);
        $blacklist->fill($request->old());

        // authorize
        if (!$request->selected_customer->can('create', $blacklist)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists.create',
                'blacklist' => $blacklist
            ]);
        }
        return view('blacklists.create', [
            'blacklist' => $blacklist,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        // Get current user
        $current_user = $request->user();
        $blacklist = new \Acelle\Model\Blacklist();

        // authorize
        if (!$request->selected_customer->can('create', $blacklist)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            $this->validate($request, \Acelle\Model\Blacklist::rules());

            // Save current user info
            $blacklist->fill($request->all());
            $blacklist->customer_id = $request->selected_customer->id;
            $blacklist->status = 'active';

            if ($blacklist->save()) {
                // Log
                $blacklist->log('created', $request->selected_customer);

                $request->session()->flash('alert-success', trans('messages.blacklist.created'));
                if ($request->wantsJson()) {
                    return response()->json([
                        'redirectAction' => 'BlacklistController@index'
                    ]);
                }
                return redirect()->action('BlacklistController@index');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        $blacklist = \Acelle\Model\Blacklist::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $blacklist)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $blacklist->fill($request->old());

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists.edit',
                'blacklist' => $blacklist,
            ]);
        }
        return view('blacklists.edit', [
            'blacklist' => $blacklist,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        // Get current user
        $current_user = $request->user();
        $blacklist = \Acelle\Model\Blacklist::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $blacklist)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            $this->validate($request, \Acelle\Model\Blacklist::rules());

            // Save current user info
            $blacklist->fill($request->all());

            if ($blacklist->save()) {
                // Log
                $blacklist->log('updated', $request->selected_customer);

                $request->session()->flash('alert-success', trans('messages.blacklist.updated'));
                if ($request->wantsJson()) {
                    return response()->json([
                        'redirectAction' => 'BlacklistController@index'
                    ]);
                }
                return redirect()->action('BlacklistController@index');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete(Request $request)
    {
        if ($request->select_tool == 'all_items') {
            $request->merge(array("customer_id" => $request->selected_customer->id));
            $blacklists = $this->search($request);
        } else {
            $blacklists = \Acelle\Model\Blacklist::whereIn('id', explode(',', $request->uids));
        }

        foreach ($blacklists->get() as $blacklist) {
            // authorize
            if ($request->selected_customer->can('delete', $blacklist)) {
                // Log
                $blacklist->delist($request->selected_customer);
                $blacklist->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.blacklists.deleted');
    }

    /**
     * Start import process.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function import(Request $request)
    {
        $customer = $request->selected_customer;

        if ($request->isMethod('post')) {
            // authorize
            if (!$request->selected_customer->can('import', new \Acelle\Model\Blacklist())) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'notAuthorized'
                    ]);
                }
                return $this->notAuthorized();
            }

            if ($request->hasFile('file')) {
                // Start system job
                $job = new \Acelle\Jobs\ImportBlacklistJob($request->file('file')->path(), $request->selected_customer);
                $this->dispatch($job);
            } else {
                // @note: use try/catch instead
                echo "max_file_upload";
            }
        }

        // Get current job
        $system_job = $customer->getLastActiveImportBlacklistJob();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists.import',
                'system_job' => $system_job
            ]);
        }
        return view('blacklists.import', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Check import proccessing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function importProcess(Request $request)
    {
        $customer = $request->selected_customer;
        $system_job = \Acelle\Model\SystemJob::find($request->system_job_id);

        // authorize
        if (!$customer->can('read', new \Acelle\Model\Blacklist())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'blacklists.import_process',
                'system_job' => $system_job
            ]);
        }
        return view('blacklists.import_process', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Cancel importing job.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function cancel(Request $request)
    {
        $customer = $request->selected_customer;
        $system_job = $customer->getLastActiveImportBlacklistJob();

        // authorize
        if (!$customer->can('importCancel', new \Acelle\Model\Blacklist())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $system_job->setCancelled();

        $request->session()->flash('alert-success', trans('messages.blacklist.import.cancelled'));
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'BlacklistController@index'
            ]);
        }
        return redirect()->action('BlacklistController@index');
    }
}
