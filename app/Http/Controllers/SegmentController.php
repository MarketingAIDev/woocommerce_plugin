<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\MailList;
use Acelle\Model\Segment;
use Illuminate\Http\Request;

class SegmentController extends Controller
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
        $list = MailList::findByUid($request->list_uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments.index',
                'list' => $list,
            ]);
        }
        return view('segments.index', [
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        $list = MailList::findByUidOrFail($request->list_uid);
        $segments = Segment::search($request)->paginate($request->per_page);

        foreach ($segments as $segment) {
            $segment->updateCacheDelayed();
            $segment->segmentConditions;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments._list',
                'segments' => $segments,
                'list' => $list,
            ]);
        }
        return view('segments._list', [
            'segments' => $segments,
            'list' => $list,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $segment = new Segment();
        $segment->mail_list_id = $list->id;

        // authorize
        if (\Gate::denies('create', $segment)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'noMoreItem'
                ]);
            }
            return $this->noMoreItem();
        }

        // Get old post values
        if (isset($request->old()['conditions'])) {
            $segment->fill($request->old());

            $segment->segmentConditions = collect();
            foreach ($request->old()['conditions'] as $key => $item) {
                $condition = new \Acelle\Model\SegmentCondition();
                $condition->uid = $key;
                $condition->fill($item);
                $segment->segmentConditions->push($condition);
            }
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments.create',
                'list' => $list,
                'segment' => $segment,
            ]);
        }

        return view('segments.create', [
            'list' => $list,
            'segment' => $segment,
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
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $segment = new Segment();
        $segment->mail_list_id = $list->id;

        // authorize
        if (\Gate::denies('create', $segment)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'noMoreItem'
                ]);
            }
            return $this->noMoreItem();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = Segment::$rules;

            // addtion validates
            $empty = false;
            if (isset($request->conditions)) {
                foreach ($request->conditions as $key => $param) {
                    $rules['conditions.'.$key.'.field_id'] = 'required';
                    $rules['conditions.'.$key.'.operator'] = 'required';
                    if (!in_array($param['operator'], ['blank', 'not_blank'])) {
                        $rules['conditions.'.$key.'.value'] = 'required';
                    }
                }
            } else {
                $empty = true;
            }
            if ($empty) {
                $rules['segment_conditions_empty'] = 'required';
            }

            // Check validation
            $this->validate($request, $rules);

            // Save segment
            $segment->fill($request->all());
            $segment->save();

            // save conditions
            foreach ($request->conditions as $key => $param) {
                $condition = new \Acelle\Model\SegmentCondition();
                $condition->fill($param);
                $condition->segment_id = $segment->id;
                $field = \Acelle\Model\Field::findByUid($param['field_id']);
                if (is_object($field)) {
                    $condition->field_id = $field->id;
                } else {
                    $condition->field_id = null;
                }

                $condition->save();
            }

            // Log
            $segment->log('created', $request->selected_customer);

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.segment.created'));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'SegmentController@index',
                    'uid'=>$list->uid
                ]);
            }
            return redirect()->action('SegmentController@index', $list->uid);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $segment = Segment::findByUid($request->uid);


        $fields = $list->getFields;
        foreach ($fields as &$field){
            if(count($field->fieldOptions)){
                $field->options = $field->fieldOptions;
            }
            unset($field->fieldOptions);
        }
        unset($list->getFields);

        $segment->segmentConditions;

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'subscribers.index',
                'list' => $list,
                'segment' => $segment,
                'fields' => $fields
            ]);
        }
        return view('subscribers.index', [
            'list' => $list
        ]);
    }

    /**
     * Display segment's subscribers.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribers(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $segment = Segment::findByUid($request->uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments.subscribers',
                'subscribers' => $segment->subscribers(),
                'list' => $list,
                'segment' => $segment,
            ]);
        }
        return view('segments.subscribers', [
            'subscribers' => $segment->subscribers(),
            'list' => $list,
            'segment' => $segment,
        ]);
    }

    /**
     * Display segment's subscribers.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function listing_subscribers(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $segment = Segment::findByUid($request->uid);

        $subscribers = $segment->subscribers($request);
        // $total = distinctCount($subscribers, 'subscribers.id');
        $total = $subscribers->count();

        $subscribers = \optimized_paginate($subscribers, $request->per_page ?? 20, null, null, null, $total);

        $fields = $list->getFields->whereIn('uid', explode(',', $request->columns));
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'subscribers._list',
                'subscribers' => $subscribers,
                'total' => $total,
                'list' => $list,
                'fields' => $fields,
                'segment' => $segment,
            ]);
        }
        return view('subscribers._list', [
            'subscribers' => $subscribers,
            'total' => $total,
            'list' => $list,
            'fields' => $fields,
            'segment' => $segment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $segment = Segment::findByUid($request->uid);

        // authorize
        if (\Gate::denies('update', $segment)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // Get old post values
        if (isset($request->old()['conditions'])) {
            $segment->fill($request->old());

            $segment->segmentConditions = collect([]);
            foreach ($request->old()['conditions'] as $key => $item) {
                $condition = new \Acelle\Model\SegmentCondition();
                $condition->uid = $key;
                $condition->fill($item);
                $segment->segmentConditions->push($condition);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments.edit',
                'list' => $list,
                'segment' => $segment,
            ]);
        }
        return view('segments.edit', [
            'list' => $list,
            'segment' => $segment,
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
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $segment = Segment::findByUid($request->uid);

        // authorize
        if (\Gate::denies('update', $segment)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('patch')) {
            $rules = Segment::$rules;

            // addtion validates
            $empty = false;
            if (isset($request->conditions)) {
                foreach ($request->conditions as $key => $param) {
                    $rules['conditions.'.$key.'.field_id'] = 'required';
                    $rules['conditions.'.$key.'.operator'] = 'required';
                    if (!in_array($param['operator'], ['blank', 'not_blank'])) {
                        $rules['conditions.'.$key.'.value'] = 'required';
                    }
                    if(strpos($param['field_id'], "__shopify")===0){
                        $rules['conditions.'.$key.'.time_period'] = 'required';
                        if(strpos($param['time_period'], "in_last")===0){
                            $rules['conditions.'.$key.'.time_period_in_last_days'] = 'required';
                        }
                    }
                }
            } else {
                $empty = true;
            }
            if ($empty) {
                $rules['segment_conditions_empty'] = 'required';
            }

            // Check validation
            $this->validate($request, $rules);

            // Save segment
            $segment->fill($request->all());
            $segment->save();
            // save conditions
            $segment->segmentConditions()->delete();
            foreach ($request->conditions as $key => $param) {
                $condition = new \Acelle\Model\SegmentCondition();
                $condition->fill($param);
                $condition->segment_id = $segment->id;
                $field = \Acelle\Model\Field::findByUid($param['field_id']);
                if (is_object($field)) {
                    $condition->field_id = $field->id;
                } else {
                    $condition->field_id = null;
                }

                $condition->save();
            }

            // Log
            $segment->log('updated', $request->selected_customer);

            // update cached information
            event(new \Acelle\Events\MailListUpdated($segment->mailList));

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.segment.updated'));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'SegmentController@index',
                    'uid'=>$list->uid
                ]);
            }
            return redirect()->action('SegmentController@index', $list->uid);
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items = Segment::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if (\Gate::denies('delete', $item)) {
                return;
            }
        }

        foreach ($items->get() as $item) {
            $item->delete();

            // Log
            $item->log('deleted', $request->selected_customer);
        }

        // Redirect to my lists page
        // Redirect to my lists page
        echo trans('messages.segments.deleted');
    }

    /**
     * Get sample option line.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function sample_condition(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments._sample_condition',
                'list' => $list,
            ]);
        }
        return view('segments._sample_condition', [
            'list' => $list,
        ]);
    }

    /**
     * Select box with list.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function selectBox(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments._select_box',
                'options' => collect($list->readCache('SegmentSelectOptions', [])),
                'index' => $request->index
            ]);
        }
        return view('segments._select_box', [
            'options' => collect($list->readCache('SegmentSelectOptions', [])),
            'index' => $request->index,
        ]);
    }

    /**
     * Segment condition value control.
     *
     * @param int $field_uid
     *
     * @return \Illuminate\Http\Response
     */
    public function conditionValueControl(Request $request)
    {
        $field = \Acelle\Model\Field::findByUid($request->field_uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments._condition_value_control',
                'operator' => $request->operator,
                'field_uid' => $request->field_uid,
                'field' => $field,
                'index' => $request->index,
            ]);
        }
        return view('segments._condition_value_control', [
            'operator' => $request->operator,
            'field_uid' => $request->field_uid,
            'field' => $field,
            'index' => $request->index,
        ]);
    }


    /**
     * Export to csv.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (\Gate::denies('export', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        // Start system job
        $job = new \Acelle\Jobs\ExportSegmentsJob($list, $request->selected_customer, $request->input("fields"), $request->uid);
        $this->dispatch($job);

        // Action Log
        $list->log('export_started', $request->selected_customer);
    }



    /**
     * Export to csv.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function viewExport(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (\Gate::denies('export', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $system_jobs = $list->exportSegmentJobs($request->uid);
        $segment = Segment::findByUid($request->uid);


        // authorize
        if (\Gate::denies('export', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'segments.export',
                'list' => $list,
                'segment' => $segment,
                'system_jobs' => $system_jobs
            ]);
        }
        return view('segments.export', [
            'list' => $list,
            'segment' => $segment,
            'system_jobs' => $system_jobs
        ]);
    }

    /**
     * Display a listing of segment export  job.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportList(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (\Gate::denies('export', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $system_jobs = $list->exportSegmentJobs($request->uid);
        $system_jobs = $system_jobs->orderBy($request->sort_order, $request->sort_direction);
        $system_jobs = $system_jobs->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'helpers._export_list',
                'system_jobs' => $system_jobs,
                'list' => $list,
                'label' => trans('messages.export_segments')
            ]);
        }
        return view('helpers._export_list', [
            'system_jobs' => $system_jobs,
            'list' => $list,
            'label' => trans('messages.export_segments')
        ]);
    }
}
