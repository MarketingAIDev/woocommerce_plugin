<?php

namespace Acelle\Http\Controllers\Admin;

use Acelle\Model\ClickLog;
use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class ClickLogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('report_click_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = ClickLog::getAll();

        return view('admin.click_logs.index', [
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
        if ($request->user()->admin->getPermission('report_click_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = ClickLog::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.click_logs._list', [
            'items' => $items,
        ]);
    }
}
