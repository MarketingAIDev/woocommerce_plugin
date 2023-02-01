<?php

namespace Acelle\Http\Controllers\Admin;

use Acelle\Model\FeedbackLog;
use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class FeedbackLogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('report_feedback_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = FeedbackLog::getAll();

        return view('admin.feedback_logs.index', [
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
        if ($request->user()->admin->getPermission('report_feedback_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = FeedbackLog::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.feedback_logs._list', [
            'items' => $items,
        ]);
    }
}
