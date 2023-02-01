<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\SendingDomain;
use Acelle\Model\SendingServer;

class SendingDomainController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function index(Request $request)
    {
        if (!$request->selected_customer->can('read', new SendingDomain())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        //$plan = $request->selected_customer->activeSubscription()->plan;

//        if ($plan->useSystemSendingServer()) {
//            $server = $plan->primarySendingServer();
//        } else {
//            $server = null;
//        }

        //$items = SendingDomain::search($request, $server);
        $items = SendingDomain::search($request, null);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains.index',
                'items' => $items,
            ]);
        }
        return view('sending_domains.index', [
            'items' => $items,
        ]);
    }


    public function listing(Request $request)
    {
        if (!$request->selected_customer->can('read', new SendingDomain())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
//        $plan = $request->selected_customer->activeSubscription()->plan;
//
//        if ($plan->useSystemSendingServer()) {
//            $server = $plan->primarySendingServer();
//        } else {
//            $server = null;
//        }
        
        //$items = SendingDomain::search($request, $server)->paginate($request->per_page);
        $items = SendingDomain::search($request, null)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains._list',
                'items' => $items,
            ]);
        }
        return view('sending_domains._list', [
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $server = new SendingDomain([
            'signing_enabled' => true,
        ]);
        $server->status = 'active';
        $server->uid = '0';
        $server->fill($request->old());

        // authorize
        if (!$request->selected_customer->can('create', $server)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains.create',
                'server' => $server,
                'readonly' => '0',
            ]);
        }
        return view('sending_domains.create', [
            'server' => $server,
            'readonly' => '0',
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
        $domain = new SendingDomain();

        // authorize
        if (!$request->selected_customer->can('create', $domain)) {
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

        // save posted data
        $this->validate($request, SendingDomain::rules());

        // Save current user info
        $domain->fill($request->all());
        $domain->customer_id = $customer->id;
        $domain->status = 'active';

        if ($domain->save()) {
//            if ($plan->useSystemSendingServer()) {
//                $server = $plan->primarySendingServer();
//                if ($server->allowVerifyingOwnDomainsRemotely()) {
//                    $domain->verifyWith($server);
//                }
//            }

            $domain->syncToMta();

            // Log
            $domain->log('created', $request->selected_customer);

            $request->session()->flash('alert-success', trans('messages.sending_domain.created'));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'SendingDomainController@show',
                    'uid' => $domain->uid,
                ]);
            }
            return redirect()->action('SendingDomainController@show', $domain->uid);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $domain = SendingDomain::findByUid($id);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains.show',
                'server' => $domain,
                'records' => $domain->getAllRecords(),
                'readonly' => 'readonly'
            ]);
        }

        return view('sending_domains.show', [
            'server' => $domain,
            'readonly' => 'readonly',
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
        $server = SendingDomain::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $server->fill($request->old());

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains.edit',
                'server' => $server,
                'readonly' => 'readonly'
            ]);
        }
        return view('sending_domains.edit', [
            'server' => $server,
            'readonly' => 'readonly',
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
        $server = SendingDomain::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            $this->validate($request, SendingDomain::rules());

            // Save current user info
            $server->fill($request->all());

            if ($server->save()) {
                // Log
                $server->log('updated', $request->selected_customer);

                $request->session()->flash('alert-success', trans('messages.sending_domain.updated'));
                if ($request->wantsJson()) {
                    return response()->json([
                        'redirectAction' => 'SendingDomainController@show',
                        'uid' => $server->uid
                    ]);
                }
                return redirect()->action('SendingDomainController@show', $server->uid);
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
            $item = SendingDomain::findByUid($row[0]);

            // authorize
            if (!$request->selected_customer->can('update', $item)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'notAuthorized',
                    ]);
                }
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->save();
        }

        echo trans('messages.sending_domain.custom_order.updated');
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
        $items = SendingDomain::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->selected_customer->can('delete', $item)) {
                // Log
                $item->log('deleted', $request->selected_customer);

                $item->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_domains.deleted');
    }

    /**
     * Verify sending domain.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id)
    {
        $domain = SendingDomain::findByUid($id);
        $domain->verify();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'sending_domains._records',
                'records' => $domain->getAllRecords(),
                'server' => $domain
            ]);
        }
    }

    /**
     * sending domain's records.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request, $id)
    {
        $domain = SendingDomain::findByUid($id);

        if ($domain->isAssociatedWithSendingServer()) {
            $options = $domain->getOptions()['verification'];
            $identity = $options['identity'];
            $dkims = $options['dkim'];
            $spf = array_key_exists('spf', $options) ? $options['spf'] : [];

            return view('sending_domains._records_aws', [
                'domain' => $domain,
                'identity' => $identity,
                'dkims' => $dkims,
                'spf' => $spf,
            ]);
        } else {

            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'sending_domains._records',
                    'records' => $domain->getAllRecords(),
                    'server' => $domain
                ]);
            }
            return view('sending_domains._records', [
                'server' => $domain,
            ]);
        }
    }

    /**
     * update VerificationTxtName.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function updateVerificationTxtName($id, Request $request)
    {
        $server = SendingDomain::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if (!$server->setVerificationTxtName($request->value)) {
            return response(trans('messages.sending_domain.verification_hostname.not_valid'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * update VerificationTxtName.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDkimSelector($id, Request $request)
    {
        $server = SendingDomain::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('update', $server)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if (!$server->setDkimSelector($request->value)) {
            return response(trans('messages.sending_domain.dkim_selector.not_valid'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }
}
