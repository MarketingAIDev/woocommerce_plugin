<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Field;
use Acelle\Model\MailList;
use Illuminate\Http\Request;

class FieldController extends Controller
{

    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $list = MailList::findByUidOrFail($request->list_uid);
        $fields = $list->getFields;

        // authorize
        if (\Gate::denies('update', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // Get old post values
        if (isset($request->old()['fields'])) {
            $fields = collect();
            foreach ($request->old()['fields'] as $key => $item) {
                $field = Field::findByUid($item['uid']);
                if (!is_object($field)) {
                    $field = new Field();
                    $field->uid = $key;
                }
                $field->fill($item);

                // If email field
                if ($list->getEmailField()->uid == $field->uid) {
                    $field = $list->getEmailField();
                    $field->label = $item['label'];
                    $field->default_value = $item['default_value'];
                }

                // Field options
                if (isset($item['options'])) {
                    $field->fieldOptions = collect();
                    foreach ($item['options'] as $key2 => $item2) {
                        $option = new \Acelle\Model\FieldOption($item2);
                        $option->uid = $key2;
                        $field->fieldOptions->push($option);
                    }
                }

                $fields[] = $field;
            }
        }

        if ($request->wantsJson()) {
            foreach ($fields as &$field){
                if(count($field->fieldOptions)){
                    $field->options = $field->fieldOptions;
                }
                unset($field->fieldOptions);
            }
            unset($list->getFields);
            return response()->json([
                'view' => 'fields.index',
                'list' => $list,
                'fields' => $fields,
            ]);
        }
        return view('fields.index', [
            'list' => $list,
            'fields' => $fields,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
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
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (\Gate::denies('update', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = [];

            // Check if filed does not have EMAIL tag
            $conflict_tag = false;
            $tags = [];
            foreach ($request->fields as $key => $item) {
                // If email field
                if ($list->getEmailField()->uid != $item['uid']) {
                    // check required input
                    $rules['fields.' . $key . '.label'] = 'required';
                    $rules['fields.' . $key . '.tag'] = 'required|alpha_dash';

                    // check field options
                    if (isset($item['options'])) {
                        foreach ($item['options'] as $key2 => $item2) {
                            $rules['fields.' . $key . '.options.' . $key2 . '.label'] = 'required';
                            $rules['fields.' . $key . '.options.' . $key2 . '.value'] = 'required';
                        }
                    }

                    // Check tag exsit
                    $tag = Field::formatTag($item['tag']);
                    if (in_array($tag, $tags)) {
                        $conflict_tag = true;
                    }
                    $tags[] = $tag;
                }
            }
            if ($conflict_tag) {
                $rules['conflict_field_tags'] = 'required';
            }
            $this->validate($request, $rules);

            // Save fields
            $saved_ids = [];
            foreach ($request->fields as $uid => $item) {
                $field = Field::findByUid($item['uid']);
                if (!is_object($field)) {
                    $field = new Field();
                    $field->mail_list_id = $list->id;
                }

                // If email field
                if ($list->getEmailField()->uid != $field->uid) {
                    // save exsit field
                    $item['tag'] = Field::formatTag($item['tag']);
                    $field->fill($item);
                    $field->save();

                    // save field options
                    $field->fieldOptions()->delete();
                    if (isset($item['options'])) {
                        foreach ($item['options'] as $key2 => $item2) {
                            $option = new \Acelle\Model\FieldOption($item2);
                            $option->field_id = $field->id;
                            $option->save();
                        }
                    }
                } else {
                    $field->label = $item['label'];
                    $field->custom_order = $item['custom_order'];
                    $field->default_value = $item['default_value'];
                    $field->save();
                }

                // store save ids
                $saved_ids[] = $field->uid;
            }
            // Delete fields
            foreach ($list->getFields as $field) {
                if (!in_array($field->uid, $saved_ids) && $field->uid != $list->getEmailField()->uid) {
                    $field->delete();
                }
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.fields.updated'));
            if ($request->wantsJson()) {
                $fields = $list->getFields;
                foreach ($fields as &$field){
                    if(count($field->fieldOptions)){
                        $field->options = $field->fieldOptions;
                    }
                    unset($field->fieldOptions);
                }
                unset($list->getFields);
                return response()->json([
                    'uid' => $list->uid,
                    'list' => $list,
                    'fields' => $fields,
                ]);
            }
            return redirect()->action('FieldController@index', $list->uid);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $field = Field::findByUid($row[0]);

            // authorize
            if (\Gate::denies('update', $field->mailList)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'notAuthorized'
                    ]);
                }
                return $this->notAuthorized();
            }

            $field->custom_order = $row[1];
            $field->save();
        }

        echo trans('messages.fields.custom_order.updated');
    }

    /**
     * Get field sample.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function sample(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (\Gate::denies('update', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'fields._form_samples',
                'list' => $list,
                'type' => $request->type,
            ]);
        }
        return view('fields._form_samples', [
            'list' => $list,
            'type' => $request->type,
        ]);
    }

    /**
     * Delete an item.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function delete(Request $request)
    {
        $field = Field::findByUid($request->uid);

        // authorize
        if (\Gate::denies('update', $field->mailList)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($field->tag != 'EMAIL') {
            $field->delete();

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.fields.deleted'));
            return redirect()->action('FieldController@index', $request->list_uid);
        } else {
            // Redirect to my lists page
            $request->session()->flash('alert-error', trans('messages.fields.can_not_delete_email_field'));
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'FieldController@index',
                    'uid' => $request->list_uid
                ]);
            }
            return redirect()->action('FieldController@index', $request->list_uid);
        }
    }
}
