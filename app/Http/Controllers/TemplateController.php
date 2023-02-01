<?php

namespace Acelle\Http\Controllers;

use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\Request;
use Acelle\Model\Template;
use Acelle\Model\Setting;
use App;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function index(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $templates = Template::search($request);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.index',
                'templates' => $templates,
            ]);
        }
        return view('templates.index', [
            'templates' => $templates,
        ]);
    }

    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $templates = Template::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates._list',
                'templates' => $templates,
            ]);
        }
        return view('templates._list', [
            'templates' => $templates,
        ]);
    }

    public function choosing(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $templates = Template::search($request)->paginate($request->per_page);
        $campaign = \Acelle\Model\Campaign::findByUid($request->campaign_uid);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates._list_choose',
                'templates' => $templates,
                'campaign' => $campaign,
            ]);
        }
        return view('templates._list_choose', [
            'templates' => $templates,
            'campaign' => $campaign,
        ]);
    }

    public function content(Request $request)
    {
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->selected_customer->can('view', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        echo $template->content;
    }

    public function create(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = new Template();

        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.create',
                'template' => $template,
            ]);
        }
        return view('templates.create', [
            'template' => $template,
        ]);
    }

    public function store(Request $request)
    {
        // Generate info
        $user = $request->user();
        $customer = $request->selected_customer;

        $template = new Template();
        $template->customer_id = $customer->id;

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            $this->validate($request, $rules);

            // Save template
            $template->fill($request->all());
            $template->source = 'editor';
            if (isset($request->source)) {
                $template->source = $request->source;
            }

            //// update content
            //$template->content = preg_replace('/href\=\'([^\']*\{)/',"href='{", $template->content);
            //$template->content = preg_replace('/href\=\"([^\"]*\{)/','href="{', $template->content);
            $template->save();
            $template->untransform();
            $template->save();

            if (!empty(Setting::get('storage.s3'))) {
                App::make('xstore')->store($template);
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.template.created'));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'TemplateController@index',
                ]);
            }
            return redirect()->action('TemplateController@index');
        }
    }

    public function show($id)
    {
    }

    public function edit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.edit',
                'template' => $template,
            ]);
        }
        return view('templates.contentbuilder', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('patch')) {
            // Save template
            $template->fill($request->all());

            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            // make validator
            $validator = \Validator::make($request->all(), $rules);

            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }

            $template->untransform();
            $template->save();

            // success
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'TemplateController@index',
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.updated'),
            ], 201);
        }
    }

    public function destroy($id)
    {
    }

    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = Template::findByUid($row[0]);

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
            $item->untransform();
            $item->save();
        }

        echo trans('messages.templates.custom_order.updated');
    }

    public function upload(Request $request)
    {
        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $template = Template::upload($request);

            if (!empty(Setting::get('storage.s3'))) {
                App::make('xstore')->store($template);
            }

            $request->session()->flash('alert-success', trans('messages.template.uploaded'));
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'TemplateController@index',
                    'uid' => $template->uid
                ]);
            }
            return redirect()->action('TemplateController@index');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.upload',
            ]);
        }
        return view('templates.upload');
    }

    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json([
                'status' => 'notice',
                'message' => trans('messages.operation_not_allowed_in_demo'),
            ]);
        }

        $items = Template::whereIn('uid', explode(',', $request->uids));
        $count = 0;

        foreach ($items->get() as $item) {
            // authorize
            if ($request->selected_customer->can('delete', $item)) {
                $item->delete();
                $count++;
            }
        }

        if ($request->wantsJson()) {
            $s = $count != 1 ? "s" : "";
            return response()->json([
                'message' => "$count template$s deleted"
            ]);
        }

        // Redirect to my lists page
        echo trans('messages.templates.deleted');
    }


    public function preview(Request $request, $id)
    {
        $template = Template::findByUid($id);

        // authorize
        if (!$request->selected_customer->can('preview', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->not_authorized();
        }

        return view('templates.preview', [
            'template' => $template,
        ]);
    }

    /**
     * Save template screenshot.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function saveImage(Request $request, $uid)
    {
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('saveImage', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->not_authorized();
        }

        // if thumb is default
        if (substr($template->image, 0, 1) == '/') {
            return;
        }

        $upload_loca = 'app/email_templates/';
        $upload_path = storage_path($upload_loca);
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $filename = 'screenshot-' . $uid . '.png';

        // remove "data:image/png;base64,"
        $uri = substr($request->data, strpos($request->data, ',') + 1);

        // save to file
        file_put_contents($upload_path . $filename, base64_decode($uri));

        // create thumbnails
        $img = \Image::make($upload_path . $filename);
        $img->fit(178, 200)->save($template->getStoragePath('thumbnail.png'));
        unlink($upload_path . $filename);
        // save
        $template->image = route('template_assets', ['uid' => $template->uid, 'name' => 'thumbnail.png'], false);
        $template->untransform();
        $template->save();
    }

    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function build(Request $request)
    {
        $template = new Template();
        $template->name = trans('messages.untitled_template');

        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $elements = [];
        if (isset($request->style)) {
            $elements = Template::templateStyles()[$request->style];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.build',
                'template' => $template,
                'elements' => $elements
            ]);
        }
        return view('templates.build', [
            'template' => $template,
            'elements' => $elements
        ]);
    }

    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function rebuild(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.rebuild',
                'template' => $template,
            ]);
        }
        return view('templates.rebuild', [
            'template' => $template,
        ]);
    }

    /**
     * Select template style.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function buildSelect(Request $request)
    {
        $template = new Template();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.build_start',
                'template' => $template,
            ]);
        }
        return view('templates.build_start', [
            'template' => $template,
        ]);
    }

    /**
     * Copy template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function copy(Request $request)
    {
        $template = Template::findByUid($request->uid);

        if ($request->isMethod('post')) {
            // authorize
            if (!$request->selected_customer->can('copy', $template)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'notAuthorized',
                    ]);
                }
                return $this->notAuthorized();
            }

            $template->copy($request->name, $request->selected_customer);

            echo trans('messages.template.copied');
            return;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.copy',
                'template' => $template,
            ]);
        }
        return view('templates.copy', [
            'template' => $template,
        ]);
    }

    public function builderEdit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        if (!$template) {
            throw new NotFoundException();
        }

        // authorize
        if (!$request->selected_customer->id != $template->customer->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $data = $this->validate($request, $rules);
            $template->content = $data['content'];
            $template->untransform();
            $template->save();

            return response()->json([
                'status' => 'success',
            ]);
        }

        $this->setReferrer(self::class);
        $backURL = $this->getReferrer(self::class, action([self::class, 'index']));

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.builder.edit',
                'template' => $template,
                'templates' => $template->getBuilderTemplates($request->selected_customer),
                'backURL' => $backURL
            ]);
        }
        return view('templates.contentbuilder', [
            'template' => $template,
            'templates' => $template->getBuilderTemplates($request->selected_customer),
            'backURL' => $backURL
        ]);
    }

    public function contentBuilderSave(Request $request, $uid)
    {
        // Generate info
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $data = $this->validate($request, $rules);
            $template->content = $data['content'];
            $template->save();
            $template->saveImages();
            $template->save();

            return response()->json([
                'status' => 'success',
            ]);
        }
    }

    /**
     * Change template from exist template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderChangeTemplate(Request $request, $uid, $change_uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);
        $changeTemplate = Template::findByUid($change_uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $template->changeTemplate($changeTemplate);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderEditContent(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.builder.content',
                'content' => $template->render(),
            ]);
        }
        return view('templates.builder.content', [
            'content' => $template->render(),
        ]);
    }

    /**
     * Upload asset to builder.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderAsset(Request $request, $uid)
    {
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $filename = $template->uploadAsset($request->file('file'));

        return response()->json([
            'url' => route('customer_files', ['uid' => $request->user()->uid, 'name' => $filename])
        ]);
    }

    public function builderCreate(Request $request)
    {
        $customer = $request->selected_customer;

        $template = new Template();
        $template->name = trans('messages.untitled_template');

        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Gallery
        $templates = Template::where('customer_id', '=', null);

        // validate and save posted data
        if ($request->isMethod('post')) {

            if ($request->expectsJson()) {
                if (!$request->layout && !$request->template) {
                    return response()->json([
                        'message' => "Please select a layout or template"
                    ], 419);
                }
                if (!$request->name) {
                    return response()->json([
                        'message' => "Please enter a name"
                    ], 419);
                }
            }

            if ($request->layout) {
                $template = new Template();
                $template->customer_id = $customer->id;
                $template->name = $request->name;
                $template->save();
                $template->untransform();

                $template->addTemplateFromLayout($request->layout);
            } else if ($request->template) {
                $currentTemplate = Template::findByUid($request->template);

                $template = $currentTemplate->copy($request->name, $customer);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    "uid" => $template->uid
                ], 201);
            }
            return redirect()->action('TemplateController@index');
        }

        return view('templates.builder.create', [
            'template' => $template,
            'templates' => $templates,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function builderTemplates(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $templates = Template::search($request)->paginate($request->per_page);

        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'templates.builder.templates',
                'templates' => $templates,
            ]);
        }
        return view('templates.builder.templates', [
            'templates' => $templates,
        ]);
    }

    /**
     * Update template thumb.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumb(Request $request, $uid)
    {
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumb', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // update thumb
            $template->uploadThumbnail($request->file);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('templates.updateThumb', [
            'template' => $template,
        ]);
    }

    /**
     * Update template thumb url.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumbUrl(Request $request, $uid)
    {
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'url' => 'required|url',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumbUrl', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // update thumb
            $template->uploadThumbnailUrl($request->url);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('templates.updateThumbUrl', [
            'template' => $template,
        ]);
    }

    public function templateLayouts(Request $request)
    {
        // authorize
        if (!$request->selected_customer->can('create', Template::class)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }
        $template_styles = Template::templateStyles();
        $template_layouts = [];
        foreach ($template_styles as $name => $style)
            $template_layouts[] = array(
                "name" => $name,
                "title" => trans('messages.' . $name)
            );

        return response()->json([
            'template_layouts' => $template_layouts,
        ]);
    }

    public function tags()
    {
        $tags = Template::tags();
        return view('ew_tags', ['tags' => $tags]);
    }
}
