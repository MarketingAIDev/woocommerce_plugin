<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Select2 plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function select2(Request $request)
    {
        echo \Acelle\Model\Plan::select2($request);
    }

    public function listing(Request $request)
    {
        return Plan::where('price', '>', 0)->paginate($request->per_page ?? 20);
    }
}
