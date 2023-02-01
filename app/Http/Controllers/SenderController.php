<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;
use Acelle\Model\Sender;
use Illuminate\Support\Facades\Gate;

class SenderController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function search($request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        return Sender::search($request);
    }

    public function index(Request $request)
    {
        if (Gate::denies('listing', new Sender())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        /** @var Customer $customer */
        $customer = request()->selected_customer;
        $plan = $customer->getActiveShopifyRecurringApplicationCharge()->plan;

        if ($plan->useOwnSendingServer()) {
            $email = true;
            $domain = true;
        } else {
            $server = $plan->primarySendingServer();
            $email = $server->allowVerifyingOwnEmails() || $server->allowVerifyingOwnEmailsRemotely();
            $domain = $server->allowVerifyingOwnDomains() || $server->allowVerifyingOwnDomainsRemotely();
        }

        if (!$email && !$domain) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'senders.available',
                    'identities' => $plan->getVerifiedIdentities(),
                ]);
            }
            return view('senders.available', [
                'identities' => $plan->getVerifiedIdentities(),
            ]);
        }

        if ($domain && !$email) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectURL' => url('sending_domains'),
                ]);
            }
            return redirect(url('sending_domains'));
        } else {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'senders.index',
                    'senders' => $this->search($request)
                ]);
            }
            return view('senders.index', [
                'senders' => $this->search($request),
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if (\Gate::denies('listing', new Sender())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders._list',
                'senders' => $this->search($request)->paginate($request->per_page),
            ]);
        }
        return view('senders._list', [
            'senders' => $this->search($request)->paginate($request->per_page),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $sender = new Sender();

        $sender->fill($request->old());

        // authorize
        if (\Gate::denies('create', $sender)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders.create',
                'sender' => $sender,
            ]);
        }
        return view('senders.create', [
            'sender' => $sender,
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
        $sender = new Sender();

        // authorize
        if (\Gate::denies('create', $sender)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $plan = $request->selected_customer->activeSubscription()->plan;

        $sender->fill($request->all());
        $sender->customer_id = $request->selected_customer->id;
        $sender->status = Sender::STATUS_PENDING;

        $this->validate($request, $sender->rules());

        try {
            $sender->save();

            if ($plan->useSystemSendingServer()) {
                $server = $plan->primarySendingServer();
            } else {
                $server = null;
            }

            $sender->verifyWith($server);
            return redirect()->action('SenderController@show', $sender->uid);
        } catch (\Exception $ex) {
            $sender->delete();
            $request->session()->flash('alert-error', $ex->getMessage());
            return redirect()->action('SenderController@index');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $sender = Sender::findByUid($id);
        $sender->updateVerificationStatus();

        // authorize
        if (\Gate::denies('read', $sender)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders.show',
                'sender' => $sender,
                'verificationOptions' => Sender::verificationTypeSelectOptions($server)
            ]);
        }
        return view('senders.show', [
            'sender' => $sender,
        ]);
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
        $sender = Sender::findByUid($id);

        // authorize
        if (\Gate::denies('update', $sender)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $sender->fill($request->old());

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders.edit',
                'sender' => $sender,
            ]);
        }
        return view('senders.edit', [
            'sender' => $sender,
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
        $sender = Sender::findByUid($id);

        // authorize
        if (\Gate::denies('update', $sender)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            $sender->name = $request->name;

            $this->validate($request, $sender->editRules());

            if ($sender->save()) {
                $request->session()->flash('alert-success', trans('messages.sender.updated'));
                if ($request->wantsJson()) {
                    return response()->json([
                        'redirectAction' => 'SenderController@show',
                        'uid' => $sender->uid
                    ]);
                }
                return redirect()->action('SenderController@show', $sender->uid);
            }
        }
    }

    /**
     * Verify sender.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        try {
            $sender = Sender::verifyToken($request->token);
            return view('senders.verified', [
                'sender' => $sender,
            ]);
        } catch (\Exception $ex) {
            return view('senders.failed', [
                'message' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * Verify sender.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyResult(Request $request)
    {
        $sender = Sender::findByUid($request->uid);
        $sender->updateVerificationStatus();

        if ($sender->isVerified()) {
            return view('senders.verified', [
                'sender' => $sender,
            ]);
        } else {
            return view('senders.failed', [
                'message' => 'Failed to verify identity',
            ]);
        }
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
        if ($request->select_tool == 'all_items') {
            $senders = $this->search($request);
        } else {
            $senders = Sender::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($senders->get() as $sender) {
            // authorize
            if ($request->selected_customer->can('delete', $sender)) {
                $sender->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.senders.deleted');
    }

    /**
     * Start import process.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $customer = $request->selected_customer;

        if ($request->isMethod('post')) {
            // authorize
            if (\Gate::denies('import', new Sender())) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'notAuthorized',
                    ]);
                }
                return $this->notAuthorized();
            }

            if ($request->hasFile('file')) {
                // Start system job
                $job = new \Acelle\Jobs\ImportSenderJob($request->file('file')->path(), $request->selected_customer);
                $this->dispatch($job);
            } else {
                // @note: use try/catch instead
                echo "max_file_upload";
            }
        }

        // Get current job
        $system_job = $customer->getLastActiveImportSenderJob();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders.import',
                'system_job' => $system_job
            ]);
        }
        return view('senders.import', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Check import proccessing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function importProcess(Request $request)
    {
        $customer = $request->selected_customer;
        $system_job = \Acelle\Model\SystemJob::find($request->system_job_id);

        // authorize
        if (\Gate::denies('read', new Sender())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'senders.import_process',
                'system_job' => $system_job
            ]);
        }
        return view('senders.import_process', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Cancel importing job.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $customer = $request->selected_customer;
        $system_job = $customer->getLastActiveImportSenderJob();

        // authorize
        if (\Gate::denies('importCancel', new Sender())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $system_job->setCancelled();

        $request->session()->flash('alert-success', trans('messages.sender.import.cancelled'));
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'SenderController@index',
            ]);
        }
        return redirect()->action('SenderController@index');
    }

    /**
     * Dropbox list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dropbox(Request $request)
    {
        $droplist = $request->selected_customer->verifiedIdentitiesDroplist(strtolower(trim($request->keyword)));
        return response()->json($droplist);
    }
}
