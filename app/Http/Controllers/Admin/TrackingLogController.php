<?php

namespace Acelle\Http\Controllers\Admin;

use Acelle\Model\TrackingLog;
use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class TrackingLogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('report_tracking_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = TrackingLog::getAll();

        return view('admin.tracking_logs.index', [
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function listing(Request $request)
    {
        if ($request->user()->admin->getPermission('report_tracking_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = TrackingLog::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.tracking_logs._list', [
            'items' => $items,
        ]);
    }
}
