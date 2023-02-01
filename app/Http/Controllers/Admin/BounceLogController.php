<?php

namespace Acelle\Http\Controllers\Admin;

use Acelle\Model\BounceLog;
use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class BounceLogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('report_bounce_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = BounceLog::getAll();

        return view('admin.bounce_logs.index', [
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
        if ($request->user()->admin->getPermission('report_bounce_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = BounceLog::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.bounce_logs._list', [
            'items' => $items,
        ]);
    }
}
