<?php

namespace Acelle\Http\Controllers;

use Acelle\Events\CampaignUpdated;
use Acelle\Events\MailListUpdated;
use Acelle\Library\Tool;
use Acelle\Model\BounceLog;
use Acelle\Model\Campaign;
use Acelle\Model\ClickLog;
use Acelle\Model\Customer;
use Acelle\Model\FeedbackLog;
use Acelle\Model\IpLocation;
use Acelle\Model\Layout;
use Acelle\Model\MailList;
use Acelle\Model\OpenLog;
use Acelle\Model\Page;
use Acelle\Model\Subscriber;
use Acelle\Model\UnsubscribeLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Acelle\Library\Log as MailLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Acelle\Model\TrackingLog;
use Acelle\Library\StringHelper;
use Acelle\Model\Template;
use Acelle\Jobs\ExportCampaignLog;
use Acelle\Model\SystemJob;
use Acelle\Model\Setting;

class CampaignController extends Controller
{

    public function __construct()
    {
        $this->middleware('selected_customer')->except(['open', 'click', 'unsubscribe', 'webView']);
        parent::__construct();
    }

    public function summary(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);
        if (!$campaign)
            return response("", 404);

        $top_click_countries = [];
        foreach ($campaign->topClickCountries(7)->get() as $location) {
            $top_click_countries[] = [
                'count' => $location->aggregate,
                'name' => $location->country_name
            ];
        }

        $top_open_countries = [];
        foreach ($campaign->topCountries(7)->get() as $location) {
            $top_open_countries[] = [
                'count' => $location->aggregate,
                'name' => $location->country_name
            ];
        }

        $summary = [
            'last_open' => is_object($campaign->lastOpen()) ? Tool::formatDateTime($campaign->lastOpen()->created_at) : "",
            'open_count' => $campaign->openCount(),
            'unique_open_count' => $campaign->readCache('UniqOpenCount'),
            'unique_open_rate' => $campaign->readCache('UniqOpenRate'),
            'not_open_count' => $campaign->readCache('NotOpenCount', 0),
            'delivered_count' => $campaign->deliveredCount(),
            'subscriber_count' => $campaign->readCache('SubscriberCount', 0),
            'unsubscribe_count' => $campaign->unsubscribeCount(),
            'click_count' => $campaign->clickCount(),
            'click_rate' => $campaign->readCache('ClickedRate'),
            'bounce_count' => $campaign->clickedEmailsCount(),
            'reported_count' => $campaign->feedbackCount(),
            'top_click_countries' => $top_click_countries,
            'top_open_countries' => $top_open_countries,
        ];

        return response()->json(['summary' => $summary]);
    }

    public function index(Request $request)
    {
        $customer = $request->selected_customer;
        $campaigns = $customer->getNormalCampaigns();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.index',
                'campaigns' => $campaigns,
            ]);
        }
        return view('campaigns.index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function listing(Request $request)
    {
        $campaigns = Campaign::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'camapigns._list',
                'campaigns' => $campaigns,
            ]);
        }
        return view('campaigns._list', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $customer = $request->selected_customer;
        $campaign = new Campaign([
            'track_open' => true,
            'track_click' => true,
            'sign_dkim' => true,
        ]);

        // authorize
        if (Gate::denies('create', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'noMoreItem',
                ]);
            }
            return $this->noMoreItem();
        }

        $campaign->name = trans('messages.untitled');
        $campaign->customer_id = $customer->id;
        $campaign->status = Campaign::STATUS_NEW;
        $campaign->type = $request->type;
        $campaign->save();

        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'CampaignController@recipients',
                'uid' => $campaign->uid
            ]);
        }
        return redirect()->action('CampaignController@recipients', ['uid' => $campaign->uid]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
    }


    public function show(Request $request, $id)
    {
        $campaign = Campaign::findByUid($id);

        // Trigger the CampaignUpdate event to update the campaign cache information
        // The second parameter of the constructor function is false, meanining immediate update
        try {
            event(new CampaignUpdated($campaign));
        } catch (Exception $ex) {
            // in case TrackingLog record does not exist yet (open before logged!)
        }

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($campaign->status == 'new') {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@edit',
                    'uid' => $campaign->uid,
                    'campaign' => $campaign
                ]);
            }
            return redirect()->action('CampaignController@edit', [
                'uid' => $campaign->uid
            ]);
        } else {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@overview',
                    'uid' => $campaign->uid,
                    'campaign' => $campaign
                ]);
            }
            return redirect()->action('CampaignController@overview', [
                'uid' => $campaign->uid
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $campaign = Campaign::findByUid($id);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Check step and redirect
        if ($campaign->step() == 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@recipients',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@recipients', [
                'uid' => $campaign->uid
            ]);
        } elseif ($campaign->step() == 1) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@setup',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@setup', [
                'uid' => $campaign->uid
            ]);
        } elseif ($campaign->step() == 2) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@template',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@template', [
                'uid' => $campaign->uid
            ]);
        } elseif ($campaign->step() == 3) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@schedule',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@schedule', [
                'uid' => $campaign->uid
            ]);
        } elseif ($campaign->step() >= 4) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@confirm',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@confirm', [
                'uid' => $campaign->uid
            ]);
        }
    }


    public function update(Request $request, $id)
    {
    }


    public function destroy($id)
    {
    }

    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = Campaign::findByUid($row[0]);

            // authorize
            if (Gate::denies('sort', $item)) {
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

        echo trans('messages.campaigns.custom_order.updated');
    }

    public function recipients(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // Get rules and data
        $rules = $campaign->recipientsRules($request->all());
        $campaign->fillRecipients($request->all());

        if (!empty($request->old())) {
            $rules = $campaign->recipientsRules($request->old());
            $campaign->fillRecipients($request->old());
        }

        if ($request->isMethod('post')) {
            // Check validation
            $this->validate($request, $rules);

            $campaign->saveRecipients($request->all());

            // Trigger the CampaignUpdate event to update the campaign cache information
            // The second parameter of the constructor function is false, meanining immediate update
            event(new CampaignUpdated($campaign));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@setup',
                    'uid' => $campaign->uid
                ]);
            }
            // redirect to the next step
            return redirect()->action('CampaignController@setup', ['uid' => $campaign->uid]);
        }

        //// validate and save posted data
        //if ($request->isMethod('post')) {
        //
        //    // Check validation
        //    $this->validate($request, \Acelle\Model\Campaign::$rules);
        //
        //    // Save campaign
        //    $campaign->mail_list_id = \Acelle\Model\MailList::findByUid($request->mail_list_uid)->id;
        //    if ($request->segment_uid) {
        //        $campaign->segment_id = \Acelle\Model\Segment::findByUid($request->segment_uid)->id;
        //    } else {
        //        $campaign->segment_id = null;
        //    }
        //    $campaign->save();
        //
        //    return redirect()->action('CampaignController@setup', ['uid' => $campaign->uid]);
        //}

        if ($request->wantsJson()) {
            /** @var Customer $selected_customer */
            $selected_customer = $request->selected_customer;
            $listSelectOptions = $selected_customer->getMailListSelectOptions([], false);
            $listSelectOptions2 = [];
            foreach ($listSelectOptions as $list) {
                $mailingList = MailList::findByUid($list['value']);
                $list['segments'] = $this->selectOptionsWithLabel($mailingList->getSegmentSelectOptions(false) ?? []);
                $list['segment2s'] = $this->selectOptionsWithLabel($selected_customer->getSegment2Options($mailingList, true));
                $listSelectOptions2[] = $list;
            }

            $recipients = $campaign->getListsSegmentsGroups();
            $recipients_array = [];
            foreach ($recipients as $recipient) {
                if ($recipient['list']) {
                    $recipients_array[] = [
                        "mail_list_id" => $recipient['list']->id,
                        "mail_list_uid" => $recipient['list']->uid,
                        "is_default" => $recipient['is_default'] ?? false,
                        "segment_uids" => $recipient['segment_uids'] ?? [],
                        "segment2_uids" => $recipient['segment2_uids'] ?? [],
                    ];
                }
            }
            return response()->json([
                'view' => 'campaigns.recipients',
                'campaign' => $campaign,
                'rules' => $rules,
                'recipients' => $recipients_array,
                'list_segment_select_options' => $this->selectOptionsWithLabel($listSelectOptions2),
                'list_segment2_select_options' => $this->selectOptionsWithLabel($listSelectOptions2)
            ]);
        }
        return view('campaigns.recipients', [
            'campaign' => $campaign,
            'rules' => $rules,
        ]);
    }

    public function setup(Request $request)
    {
        $customer = $request->selected_customer;
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $campaign->from_name = !empty($campaign->from_name) ? $campaign->from_name : $campaign->defaultMailList->from_name;
        $campaign->from_email = !empty($campaign->from_email) ? $campaign->from_email : $campaign->defaultMailList->from_email;
        $campaign->subject = !empty($campaign->subject) ? $campaign->subject : $campaign->defaultMailList->default_subject;

        // Get old post values
        if ($request->old()) {
            $campaign->fillAttributes($request->old());
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            // Fill values
            $campaign->fillAttributes($request->all());

            // Check validation
            $this->validate($request, $campaign->rules($request));
            $campaign->save();

            // Log
            $campaign->log('created', $customer);

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'CampaignController@template',
                    'uid' => $campaign->uid
                ]);
            }
            return redirect()->action('CampaignController@template', [
                'uid' => $campaign->uid
            ]);
        }

        $rules = $campaign->rules();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.setup',
                'campaign' => $campaign,
                'rules' => $campaign->rules(),
                'sendingDomainOptions' => $customer->getSendingDomainOptions(),
            ]);
        }
        return view('campaigns.setup', [
            'campaign' => $campaign,
            'rules' => $campaign->rules(),
            'sendingDomainOptions' => $customer->getSendingDomainOptions(),
        ]);
    }

    public function template(Request $request)
    {
        $customer = $request->selected_customer;
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($campaign->type == 'plain-text') {
            return redirect()->action('CampaignController@plain', ['uid' => $campaign->uid]);
        }

        // check if campagin does not have template
        if (!$campaign->html) {
            return redirect()->action('CampaignController@templateCreate', ['uid' => $campaign->uid]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'campaign' => $campaign,
                'thumb_url' => $campaign->getThumbUrl() . '?tid=' . $campaign->updated_at,
            ]);
        }

        return view('campaigns.template.index', [
            'campaign' => $campaign,
            'spamscore' => Setting::isYes('spamassassin.enabled'),
        ]);

        //// Required tags
        //$validate = 'required';
        //
        //// @todo hard-coded here
        //$requiredTags = array(
        //    array('name' => '{UNSUBSCRIBE_URL}', 'required' => true),
        //);
        //
        //foreach ($requiredTags as $tag) {
        //    if ($tag['required']) {
        //        $validate .= '|substring:"'.$tag['name'].'"';
        //    }
        //}
        //
        //$rules = [];
        //
        //// if campaign type is not plain text
        //if ($campaign->type != 'plain-text' && $customer->getOption('unsubscribe_url_required') == 'yes') {
        //    $rules['html'] = $validate;
        //}
        //
        //// Get old post values
        //if (null !== $request->old()) {
        //    $campaign->fill($request->old());
        //}
        //
        //$rules = [];
        //// validate and save posted data
        //if ($request->isMethod('post')) {
        //    // Check validation
        //    $this->validate($request, $rules);
        //
        //    // Save campaign
        //    $campaign->fill($request->all());
        //    // convert html to plain text if plain text is empty
        //    if (trim($request->plain) == '') {
        //        $campaign->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($request->html)));
        //    }
        //    $campaign->save();
        //
        //    if (isset($request->template_source)) {
        //        return redirect()->action('CampaignController@templatePreview', ['uid' => $campaign->uid]);
        //    } else {
        //        return redirect()->action('CampaignController@schedule', ['uid' => $campaign->uid]);
        //    }
        //}
        //
        //// redirect page
        //if (!empty($campaign->html) || $campaign->type == 'plain-text') {
        //    return redirect()->action('CampaignController@templatePreview', ['uid' => $campaign->uid]);
        //} else {
        //    return redirect()->action('CampaignController@templateSelect', ['uid' => $campaign->uid]);
        //}
        //
        //return view('campaigns.template', [
        //    'campaign' => $campaign,
        //    'rules' => $rules,
        //]);
    }

    public function templateCreate(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.template.create',
                'campaign' => $campaign,
            ]);
        }
        return view('campaigns.template.create', [
            'campaign' => $campaign,
        ]);
    }

    public function templateSelect(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.template_select',
                'campaign' => $campaign,
            ]);
        }
        return view('campaigns.template_select', [
            'campaign' => $campaign,
        ]);
    }

    public function templateLayout(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // add layout to campaign template
        if ($request->isMethod('post')) {
            if ($request->layout) {
                $campaign->addTemplateFromLayout($request->layout);

                // update plain
                $campaign->updatePlainFromHtml();
            }

            // return redirect()->action('CampaignController@templateEdit', $campaign->uid);
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.campaign.layout.selected'),
                'url' => action('CampaignController@templateBuilderSelect', $campaign->uid),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'templates' => Template::templateStyles()
            ]);
        }

        return view('campaigns.template.layout', [
            'campaign' => $campaign,
        ]);
    }

    public function templateBuilderSelect(Request $request, $uid)
    {
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            return $this->notAuthorized();
        }

        return view('campaigns.template.templateBuilderSelect', [
            'campaign' => $campaign,
        ]);
    }

    public function templateEdit(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // save campaign html
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $this->validate($request, $rules);

            $campaign->html = $request->post('content');
            //$campaign->untransform();
            $campaign->saveImages();
            $campaign->save();

            // update plain
            $campaign->updatePlainFromHtml();

            return response()->json([
                'status' => 'success',
            ]);
        }

//        return view('campaigns.template.edit', [
//            'campaign' => $campaign,
//            'list' => $campaign->defaultMailList,
//            'templates' => $campaign->getBuilderTemplates($request->selected_customer),
//        ]);

        return view('campaigns.template.contentbuilder', [
            'campaign' => $campaign
        ]);
    }

    public function templateAsset(Request $request, $uid)
    {
        $campaign = Campaign::findByUid($request->uid);
        $filename = $campaign->uploadAsset($request->file('file'));

        return response()->json([
            'url' => route('customer_files', ['uid' => $request->user()->uid, 'name' => $filename])
        ]);
    }

    public function templateContent(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.template.content', [
            'content' => $campaign->render(),
        ]);
    }

    public function templateTheme(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            $template = Template::findByUid($request->template);
            $campaign->copyFromTemplate($template);

            // update plain
            $campaign->updatePlainFromHtml();

            // return redirect()->action('CampaignController@templateEdit', $campaign->uid);
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.campaign.theme.selected'),
                'url' => action('CampaignController@templateBuilderSelect', $campaign->uid),
            ]);
        }

        return view('campaigns.template.theme', [
            'campaign' => $campaign,
        ]);
    }

    public function templateThemeUpload(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $template = Template::upload($request);

        $campaign->copyFromTemplate($template);
        $campaign->updatePlainFromHtml();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.campaign.theme.selected')
        ]);
    }

    public function templateThemeList(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $templates = Template::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.template.themeList',
                'campaign' => $campaign,
                'templates' => $templates,
            ]);
        }
        return view('campaigns.template.themeList', [
            'campaign' => $campaign,
            'templates' => $templates,
        ]);
    }

    public function templateUpload(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $campaign->uploadTemplate($request);

            // update plain
            $campaign->updatePlainFromHtml();

            // return redirect()->action('CampaignController@template', $campaign->uid);
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.campaign.template.uploaded'),
                'url' => action('CampaignController@templateBuilderSelect', $campaign->uid),
            ]);
        }

        return view('campaigns.template.upload', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Choose an existed template.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateChoose(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);
        $template = Template::findByUid($request->template_uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $campaign->html = $template->content;
        $campaign->template_source = $template->source;
        // $campaign->plain = preg_replace('/\s+/',' ',preg_replace('/\r\n/',' ',strip_tags($campaign->html)));
        $campaign->save();

        return redirect()->action('CampaignController@templatePreview', ['uid' => $campaign->uid]);
    }

    /**
     * Choose an existed template.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function plain(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            // Check validation
            $this->validate($request, ['plain' => 'required|string']);

            // save campaign plain text
            $campaign->plain = $request->plain;
            $campaign->save();

            return redirect()->action('CampaignController@schedule', ['uid' => $campaign->uid]);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.plain',
                'campaign' => $campaign,
            ]);
        }

        return view('campaigns.plain', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Template preview.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templatePreview(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);

        $rules = [];

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.template_preview', [
            'campaign' => $campaign,
            'rules' => $rules,
        ]);
    }

    /**
     * Template preview iframe.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateIframe(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.preview', [
            'campaign' => $campaign,
        ]);
    }

    public function schedule(Request $request)
    {
        $customer = $request->selected_customer;
        $campaign = Campaign::findByUid($request->uid);

        $campaign->log('about to schedule', $customer);
        // check step
        if ($campaign->step() < 3) {
            $campaign->log('less than 3', $customer);
            return redirect()->action('CampaignController@template', ['uid' => $campaign->uid]);
        }

        // authorize
        if (Gate::denies('update', $campaign)) {
            $campaign->log('gate denied', $customer);
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $delivery_date = isset($campaign->run_at) && $campaign->run_at != '0000-00-00 00:00:00' ? Tool::dateTime($campaign->run_at)->format('Y-m-d') : Tool::dateTime(Carbon::now())->format('Y-m-d');
        $delivery_time = isset($campaign->run_at) && $campaign->run_at != '0000-00-00 00:00:00' ? Tool::dateTime($campaign->run_at)->format('H:i') : Tool::dateTime(Carbon::now())->format('H:i');

        $rules = array(
            'delivery_date' => 'required',
            'delivery_time' => 'required',
        );

        // Get old post values
        if (null !== $request->old()) {
            $campaign->fill($request->old());
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            // Check validation
            // $this->validate($request, $rules);

            $campaign->log('post request', $customer);
            //// Save campaign
            $time = Tool::systemTimeFromString($request->delivery_date . ' ' . $request->delivery_time);
            $campaign->run_at = $time;
            $campaign->save();

            // Check recipient count
            if ($campaign->subscribersCount() == 0) {
                $campaign->log('campaign doesnt have any subscribers', $customer);
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Campaign doesn\'t have any subscribers'
                    ], 422);
                }
                return redirect()->action('CampaignController@recipients', ['uid' => $campaign->uid]);
            }

            $campaign->log('requeue started', $customer);
            // Save campaign
            // @todo: check campaign status before requeuing. Otherwise, several jobs shall be created and campaign will get sent several times
            $campaign->requeue();
            $campaign->log('request ended', $customer);

            // Log
            $campaign->log('started', $customer);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => "Campaign has started successfully",
                    'campaign' => $campaign
                ]);
            }
            return redirect()->action('CampaignController@index');
        }

        try {
            $score = $campaign->score();
        } catch (Exception $e) {
            $score = null;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.schedule',
                'campaign' => $campaign,
                'schedule' => [
                    'delivery_date' => $delivery_date,
                    'delivery_time' => $delivery_time,
                ],
                'confirm' => [
                    'recipients_count' => $campaign->subscribersCount(),
                    'recipients' => $campaign->displayRecipients(),
                    'subject' => $campaign->subject,
                    'reply_to' => $campaign->reply_to,
                    'track_open' => $campaign->track_open,
                    'track_click' => $campaign->track_click,
                    'run_at' => isset($campaign->run_at) ? Tool::formatDateTime($campaign->run_at) : "",
                    'score' => $score,
                ]
            ]);
        }

        return view('campaigns.schedule', [
            'campaign' => $campaign,
            'rules' => $rules,
            'delivery_date' => $delivery_date,
            'delivery_time' => $delivery_time,
        ]);
    }

    public function confirm(Request $request)
    {
        $customer = $request->selected_customer;
        $campaign = Campaign::findByUid($request->uid);

        // check step
        if ($campaign->step() < 4) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'All steps are not complete.'
                ], 422);
            }
            return redirect()->action('CampaignController@schedule', ['uid' => $campaign->uid]);
        }

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post') && $campaign->step() >= 4) {
            // Check recipient count
            if ($campaign->subscribersCount() == 0) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Campaign doesn\'t have any subscribers'
                    ], 422);
                }
                return redirect()->action('CampaignController@recipients', ['uid' => $campaign->uid]);
            }

            // Save campaign
            // @todo: check campaign status before requeuing. Otherwise, several jobs shall be created and campaign will get sent several times
            $campaign->requeue();

            // Log
            $campaign->log('started', $customer);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => "Campaign has started successfully",
                    'campaign' => $campaign
                ]);
            }
            return redirect()->action('CampaignController@index');
        }

        try {
            $score = $campaign->score();
        } catch (Exception $e) {
            $score = null;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.confirm',
                'campaign' => $campaign,
                'confirm' => [
                    'recipients_count' => $campaign->subscribersCount(),
                    'recipients' => $campaign->displayRecipients(),
                    'subject' => $campaign->subject,
                    'reply_to' => $campaign->reply_to,
                    'track_open' => $campaign->track_open,
                    'track_click' => $campaign->track_click,
                    'run_at' => isset($campaign->run_at) ? Tool::formatDateTime($campaign->run_at) : "",
                    'score' => $score,
                ]
            ]);
        }
        return view('campaigns.confirm', [
            'campaign' => $campaign,
            'score' => $score,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $customer = $request->selected_customer;

        if (isSiteDemo()) {
            echo trans('messages.operation_not_allowed_in_demo');

            return null;
        }

        $items = Campaign::whereIn('uid', explode(',', $request->uids));
        $deleted_count = 0;

        foreach ($items->get() as $item) {
            // authorize
            if (Gate::allows('delete', $item)) {
                $item->delete();
                $deleted_count++;
                // Log
                $item->log('deleted', $customer);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $deleted_count . ' items deleted',
            ]);
        }
        echo trans('messages.campaigns.deleted');
        return null;
    }

    /**
     * Campaign overview.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function overview(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // Trigger the CampaignUpdate event to update the campaign cache information
        // The second parameter of the constructor function is false, meanining immediate update
        try {
            event(new CampaignUpdated($campaign));
        } catch (Exception $ex) {
            // in case TrackingLog record does not exist yet (open before logged!)
        }

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.confirm',
                'campaign' => $campaign,
                'overview' => [
                    'info' => [
                        'from' => $campaign->displayRecipients(),
                        'subject' => $campaign->subject,
                        'from_email' => $campaign->from_email,
                        'from_name' => $campaign->from_name,
                        'reply_to' => $campaign->reply_to,
                        'updated_at' => Tool::formatDateTime($campaign->updated_at),
                        'run_at' => isset($campaign->run_at) ? Tool::formatDateTime($campaign->run_at) : "",
                        'delivery_at' => isset($campaign->delivery_at) ? Tool::formatDateTime(Carbon::createFromFormat('Y-m-d H:i:s', $campaign->delivery_at)) : ""
                    ],
                    'statistics' => [
                        'unique_open_rate' => number_to_percentage($campaign->readCache('UniqOpenRate')),
                        'unique_open_count' => $campaign->readCache('UniqOpenCount'),
                        'open_count' => $campaign->openCount(),
                        'not_open_rate' => number_to_percentage($campaign->readCache('NotOpenRate', 0)),
                        'not_open_count' => $campaign->readCache('NotOpenCount', 0),
                        'subscriber_count' => $campaign->readCache('SubscriberCount', 0),
                        'clicked_rate' => number_to_percentage($campaign->readCache('ClickedRate')),
                        'clicked_count' => $campaign->clickedEmailsCount(),
                        'unsubscribe_rate' => number_to_percentage($campaign->unsubscribeRate()),
                        'unsubscribe_count' => $campaign->unsubscribeCount(),
                        'bounce_rate' => number_to_percentage($campaign->bounceRate()),
                        'bounce_count' => $campaign->bounceCount(),
                        'feedback_rate' => number_to_percentage($campaign->feedbackRate()),
                        'feedback_count' => $campaign->feedbackCount(),
                        'delivery_rate' => number_to_percentage($campaign->readCache('DeliveredRate')),
                        'delivery_count' => $campaign->readCache('DeliveredCount'),
                        'last_open' => is_object($campaign->lastOpen()) ? \Acelle\Library\Tool::formatDateTime($campaign->lastOpen()->created_at) : "",
                        'last_click' => is_object($campaign->lastClick()) ? \Acelle\Library\Tool::formatDateTime($campaign->lastClick()->created_at) : "",
                        'abuse_reports' => $campaign->abuseFeedbackCount()
                    ]
                ],
            ]);
        }
        return view('campaigns.overview', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Campaign links.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function links(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            $links = [];

            foreach ($campaign->getLinks() as $link) {
                $links[] = [
                    'url' => $link->url,
                    'clicks' => $link->clicks(null, $campaign)->count(),
                    'last_click' => isset($link->lastClick(null, $campaign)->created_at) ? \Acelle\Library\Tool::formatDateTime($link->lastClick(null, $campaign)->created_at) : ""
                ];
            }

            return response()->json([
                'view' => 'campaigns.confirm',
                'campaign' => $campaign,
                'links' => $links
            ]);
        }
        return view('campaigns.links', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * 24-hour chart.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function chart24h(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $result = [
            'columns' => [],
            'data' => [],
            'bar_names' => [trans('messages.opened'), trans('messages.clicked')],
        ];

        $hours = [];

        // columns
        for ($i = 23; $i >= 0; --$i) {
            $result['columns'][] = Tool::dateTime(Carbon::now())->subHours($i)->format('h:A');
            $hours[] = Tool::dateTime(Carbon::now())->subHours($i)->format('H');
        }

        // 24h collection
        $openData24h = $campaign->openUniqHours(Tool::dateTime(Carbon::now())->subHours(24), Carbon::now());
        $clickData24h = $campaign->clickHours(Tool::dateTime(Carbon::now())->subHours(24), Carbon::now());

        // datas
        foreach ($result['bar_names'] as $key => $bar) {
            $data = [];
            if ($key == 0) {
                foreach ($hours as $ohour) {
                    $num = isset($openData24h[$ohour]) ? count($openData24h[$ohour]) : 0;
                    $data[] = $num;
                }
            } else {
                foreach ($hours as $chour) {
                    $num = isset($clickData24h[$chour]) ? count($clickData24h[$chour]) : 0;
                    $data[] = $num;
                }
            }

            $result['data'][] = [
                'name' => $bar,
                'type' => 'line',
                'smooth' => true,
                'data' => $data,
                'itemStyle' => [
                    'normal' => [
                        'areaStyle' => [
                            'type' => 'default',
                        ],
                    ],
                ],
            ];
        }

        return json_encode($result);
    }

    /**
     * Chart.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function chart(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $result = [
            'columns' => [],
            'data' => [],
            'bar_names' => [
                trans('messages.recipients'),
                trans('messages.delivered'),
                trans('messages.failed'),
                trans('messages.Open'),
                trans('messages.Click'),
                trans('messages.Bounce'),
                trans('messages.report'),
                trans('messages.unsubscribe'),
            ],
        ];

        // columns
        $result['columns'][] = trans('messages.count');

        // datas
        $result['data'][] = [
            'name' => trans('messages.unsubscribe'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->unsubscribeCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#D81B60',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.report'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->feedbackCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#00897B',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.Bounce'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->bounceCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#6D4C41',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.Click'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->clickedEmailsCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#039BE5',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.Open'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->openUniqCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#546E7A',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.failed'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->failedCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#E53935',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.delivered'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->deliveredCount()],
            'itemStyle' => [
                'normal' => [
                    'color' => '#7CB342',
                ],
            ],
        ];

        $result['data'][] = [
            'name' => trans('messages.recipients'),
            'type' => 'bar',
            'smooth' => true,
            'data' => [$campaign->readCache('SubscriberCount', 0)],
            'itemStyle' => [
                'normal' => [
                    'color' => '#555',
                ],
            ],
        ];

        $result['horizontal'] = 1;

        return json_encode($result);
    }

    /**
     * Chart Country.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function chartCountry(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $result = [
            'title' => '',
            'columns' => [],
            'data' => [],
            'bar_names' => [],
        ];

        // create data
        $datas = [];
        $total = $campaign->openCount();
        $count = 0;
        foreach ($campaign->topCountries()->get() as $location) {
            $country_name = (!empty($location->country_name) ? $location->country_name : trans('messages.unknown'));
            $result['bar_names'][] = $country_name;

            $datas[] = ['value' => $location->aggregate, 'name' => $country_name];
            $count += $location->aggregate;
        }

        // others
        if ($total > $count) {
            $result['bar_names'][] = trans('messages.others');
            $datas[] = ['value' => $total - $count, 'name' => trans('messages.others')];
        }

        // datas
        $result['data'][] = [
            'name' => trans('messages.country'),
            'type' => 'pie',
            'radius' => '70%',
            'center' => ['50%', '57.5%'],
            'data' => $datas,
        ];

        $result['pie'] = 1;

        return json_encode($result);
    }

    /**
     * Chart Country by clicks.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function chartClickCountry(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $result = [
            'title' => '',
            'columns' => [],
            'data' => [],
            'bar_names' => [],
        ];

        // create data
        $datas = [];
        $total = $campaign->clickCount();
        $count = 0;
        foreach ($campaign->topClickCountries()->get() as $location) {
            $result['bar_names'][] = $location->country_name;

            $datas[] = ['value' => $location->aggregate, 'name' => $location->country_name];
            $count += $location->aggregate;
        }

        // others
        if ($total > $count) {
            $result['bar_names'][] = trans('messages.others');
            $datas[] = ['value' => $total - $count, 'name' => trans('messages.others')];
        }

        // datas
        $result['data'][] = [
            'name' => trans('messages.country'),
            'type' => 'pie',
            'radius' => '70%',
            'center' => ['50%', '57.5%'],
            'data' => $datas,
        ];

        $result['pie'] = 1;

        return json_encode($result);
    }

    /**
     * 24-hour quickView.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function quickView(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns._quick_view', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Select2 campaign.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function select2(Request $request)
    {
        $data = ['items' => [], 'more' => true];

        $data['items'][] = ['id' => 0, 'text' => trans('messages.all')];
        foreach (Campaign::getAll()->get() as $campaign) {
            $data['items'][] = ['id' => $campaign->uid, 'text' => $campaign->name];
        }

        echo json_encode($data);
    }

    /**
     * Tracking when open.
     */
    public function open(Request $request)
    {
        $messageId = StringHelper::base64UrlDecode($request->message_id);

        if (!TrackingLog::where('message_id', $messageId)->exists()) {
            LaravelLog::warning('Open tracking log not available for message: ' . $messageId);
            return response()->file(public_path('images/transparent.gif'));
        }

        $log = new OpenLog();
        $log->message_id = $messageId;
        $location = IpLocation::add($this->getRemoteIPAddress());
        $log->ip_address = $location->ip_address;
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'];

        // Trigger the CampaignUpdate event to update the campaign cache information
        // The second parameter of the constructor function is false, meanining immediate update
        try {
            $log->save();
            LaravelLog::info('Open log recorded: ' . $messageId);
            event(new MailListUpdated($log->trackingLog->subscriber->mailList));
        } catch (Exception $ex) {
            // in case TrackingLog record does not exist yet (open before logged!)
            LaravelLog::warning('Cannot save open tracking log for message: ' . $log->message_id);
            throw new Exception('Cannot save open tracking log. ' . $ex->getMessage());
        }
        # Just return a blank image
        return response()->file(public_path('images/transparent.gif'));
    }

    /**
     * Tracking when click link.
     */
    public function click(Request $request)
    {
        $decoded_url = StringHelper::base64UrlDecode($request->url);
        $messageId = StringHelper::base64UrlDecode($request->message_id);

        if (!TrackingLog::where('message_id', $messageId)->exists()) {
            LaravelLog::warning('Click tracking log not available, redirect anyway: ' . $messageId);
            return redirect()->away($decoded_url);
        }

        // redirect base64_decode($url);
        $log = new ClickLog();
        $log->message_id = $messageId;
        $location = IpLocation::add($_SERVER['REMOTE_ADDR']);
        $log->ip_address = $location->ip_address;
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $log->url = $decoded_url;

        // Trigger the CampaignUpdate event to update the campaign cache information
        // The second parameter of the constructor function is false, meanining immediate update
        try {
            $log->save();
            LaravelLog::info('Click log recorded: ' . $messageId);
            event(new MailListUpdated($log->trackingLog->subscriber->mailList));
        } catch (Exception $ex) {
            // in case TrackingLog record does not exist yet (open before logged!)
            LaravelLog::warning('Cannot save click tracking log for X message: ' . $log->message_id);
            // throw new \Exception('Cannot save click tracking log'.$ex->getMessage());
        }

        return redirect()->away($decoded_url);
    }

    /**
     * Unsubscribe url.
     */
    public function unsubscribe(Request $request)
    {
        $message_id = StringHelper::base64UrlDecode($request->message_id);
        $tracking_log = TrackingLog::where('message_id', '=', $message_id)->first();

        if (!is_object($tracking_log)) {
            LaravelLog::error('Tracking log not exists');

            return view('notice', ['message' => trans('messages.you_are_already_unsubscribed')]);
        }

        $subscriber = $tracking_log->subscriber;

        if ($subscriber->status != 'unsubscribed') {
            // Unsubcribe
            $subscriber->status = 'unsubscribed';
            $subscriber->save();

            // Page content
            $list = $subscriber->mailList;
            $layout = Layout::where('alias', 'unsubscribe_success_page')->first();
            $page = Page::findPage($list, $layout);

            $page->renderContent(null, $subscriber);

            // Unsubscribe log
            $log = new UnsubscribeLog();
            $log->message_id = $message_id;
            $location = IpLocation::add($_SERVER['REMOTE_ADDR']);
            $log->ip_address = $location->ip_address;
            $log->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $log->save();

            // Send goodbye email
            if ($list->unsubscribe_notification) {
                // SEND subscription confirmation email
                $list->sendUnsubscriptionNotificationEmail($subscriber);
            }

            if (Setting::isYes('send_notification_email_for_list_subscription')) {
                $list->sendUnsubscriptionNotificationEmailToListOwner($subscriber);
            }

            return view('pages.default', [
                'list' => $list,
                'page' => $page,
                'subscriber' => $subscriber,
            ]);
        } else {
            return view('notice', ['message' => trans('messages.you_are_already_unsubscribed')]);
        }
    }

    /**
     * Tracking logs.
     */
    public function trackingLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->trackingLogs();

        return view('campaigns.tracking_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Tracking logs ajax listing.
     */
    public function trackingLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = TrackingLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }
        return view('admin.tracking_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Download tracking logs.
     */
    public function trackingLogDownload(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $logtype = $request->input('logtype');

        $job = new ExportCampaignLog($campaign, $logtype);
        dispatch($job);

        return view('campaigns.download_tracking_log', [
            'campaign' => $campaign,
            'job' => $job->getSystemJob()
        ]);
    }

    /**
     * Tracking logs export progress.
     */
    public function trackingLogExportProgress(Request $request)
    {
        $job = SystemJob::find($request->uid);
        $json = json_decode($job->data, true);

        $output = [
            'progress' => $json['progress']
        ];

        if ($json['progress'] == 1) {
            $output['download_url'] = action('CampaignController@download', ['uid' => $job->id]);
        }

        return response()->json($output);
    }

    /**
     * Actually download.
     */
    public function download(Request $request)
    {
        $job = SystemJob::find($request->uid);
        $json = json_decode($job->data, true);
        $path = $json['path'];
        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Bounce logs.
     */
    public function bounceLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->bounceLogs();

        return view('campaigns.bounce_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Bounce logs listing.
     */
    public function bounceLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = BounceLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }
        return view('admin.bounce_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * FBL logs.
     */
    public function feedbackLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->openLogs();

        return view('campaigns.feedback_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * FBL logs listing.
     */
    public function feedbackLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = FeedbackLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }
        return view('admin.feedback_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Open logs.
     */
    public function openLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->openLogs();

        return view('campaigns.open_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Open logs listing.
     */
    public function openLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = OpenLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.open_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Click logs.
     */
    public function clickLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->clickLogs();

        return view('campaigns.click_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Click logs listing.
     */
    public function clickLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = ClickLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }

        return view('admin.click_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Unscubscribe logs.
     */
    public function unsubscribeLog(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = $campaign->unsubscribeLogs();

        return view('campaigns.unsubscribe_log', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Unscubscribe logs listing.
     */
    public function unsubscribeLogListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $items = UnsubscribeLog::search($request, $campaign)->paginate($request->per_page);

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $items,
            ]);
        }
        return view('admin.unsubscribe_logs._list', [
            'items' => $items,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Open map.
     */
    public function openMap(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            $locations = [];

            foreach ($campaign->locations()->get() as $key => $location) {
                $locations[] = [
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ];
            }

            return response()->json([
                'view' => 'campaigns.confirm',
                'campaign' => $campaign,
                'locations' => $locations
            ]);
        }
        return view('campaigns.open_map', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Delete confirm message.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteConfirm(Request $request)
    {
        $lists = Campaign::whereIn('uid', explode(',', $request->uids));

        return view('campaigns.delete_confirm', [
            'lists' => $lists,
        ]);
    }

    /**
     * Pause the specified campaign.
     *
     * @param Request $request
     */
    public function pause(Request $request)
    {
        $customer = $request->selected_customer;
        $items = Campaign::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            if (Gate::allows('pause', $item)) {
                $item->status = 'paused';
                $item->save();

                // Log
                $item->log('paused', $customer);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => trans('messages.campaigns.paused')
            ]);
        }

        return response(trans('messages.campaigns.paused'));
    }

    /**
     * Pause the specified campaign.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function restart(Request $request)
    {
        $customer = $request->selected_customer;
        $items = Campaign::whereIn('uid', explode(',', $request->input('uids', '')));

        foreach ($items->get() as $item) {
            if (Gate::allows('restart', $item)) {
                $item->requeue();

                // Log
                $item->log('restarted', $customer);
            }
        }

        // Redirect to my lists page
        echo trans('messages.campaigns.restarted');
    }

    /**
     * Subscribers list.
     */
    public function subscribers(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $subscribers = $campaign->subscribers()->groupBy('subscribers.email');

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.subscribers',
                'subscribers' => $subscribers,
                'campaign' => $campaign,
                'list' => $campaign->defaultMailList,
            ]);
        }
        return view('campaigns.subscribers', [
            'subscribers' => $subscribers,
            'campaign' => $campaign,
            'list' => $campaign->defaultMailList,
        ]);
    }

    /**
     * Subscribers listing.
     */
    public function subscribersListing(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            throw new AuthorizationException();
        }

        Subscriber::$CAMPAIGN_FOR_EXTRA_STATS = $campaign;
        $subscribers = $campaign->subscribers($request->all())->groupBy('subscribers.email')
            ->paginate($request->per_page);
        $fields = $campaign->defaultMailList->getFields->whereIn('uid', explode(',', $request->columns));

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns._subscribers_list',
                'subscribers' => $subscribers,
                'list' => $campaign->defaultMailList,
                'campaign' => $campaign,
                'fields' => $fields,
            ]);
        }
        return view('campaigns._subscribers_list', [
            'subscribers' => $subscribers,
            'list' => $campaign->defaultMailList,
            'campaign' => $campaign,
            'fields' => $fields,
        ]);
    }

    /**
     * Buiding email template.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateBuild(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
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

        return view('campaigns.template_build', [
            'campaign' => $campaign,
            'elements' => $elements,
            'list' => $campaign->defaultMailList,
        ]);
    }

    /**
     * Re-Buiding email template.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateRebuild(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.template_rebuild', [
            'campaign' => $campaign,
            'list' => $campaign->defaultMailList,
        ]);
    }

    public function copy(Request $request)
    {
        $campaign = Campaign::findByUid($request->copy_campaign_uid);

        // authorize
        if (Gate::denies('copy', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $campaign->copy($request->copy_campaign_name);

        echo trans('messages.campaign.copied');
    }

    /**
     * Send email for testing campaign.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendTestEmail(Request $request)
    {
        $campaign = Campaign::findByUid($request->send_test_email_campaign_uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $sending = $campaign->sendTestEmail($request->send_test_email);

        return json_encode($sending);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function templateList(Request $request)
    {
        $campaign = Campaign::findByUid($request->uid);
        $request->request->add(['source' => 'template']);

        $campaigns = Campaign::search($request, 'all')
            ->where('id', '!=', $campaign->id)
            ->where('html', '!=', '')
            ->paginate($request->per_page);

        return view('campaigns._template_list', [
            'campaigns' => $campaigns,
            'uid' => $campaign->uid,
        ]);
    }

    /**
     * Preview template.
     *
     * @param int $id
     *
     * @return Response
     */
    public function preview($id)
    {
        $campaign = Campaign::findByUid($id);

        // authorize
        if (Gate::denies('preview', $campaign)) {
            return $this->not_authorized();
        }

        return view('campaigns.preview', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Save campaign screenshot.
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function saveImage(Request $request, $uid)
    {
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (Gate::denies('saveImage', $campaign)) {
            return $this->not_authorized();
        }

        $upload_loca = 'app/campaign_templates/';
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
        $img = Image::make($upload_path . $filename);
        $img->fit(178, 200)->save($campaign->getStoragePath('thumbnail.png'));
        unlink($upload_path . $filename);

        // save
        $campaign->image = route('campaign_assets', ['uid' => $campaign->uid, 'name' => 'thumbnail.png'], false);
        $campaign->save();
    }

    public function campaignTemplateChoose(Request $request)
    {
        $user = $request->user();
        $campaign = Campaign::findByUid($request->uid);
        $from_campaign = Campaign::findByUid($request->from_uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $campaign->html = $from_campaign->html;
        $campaign->template_source = $from_campaign->template_source;
        // $campaign->plain = preg_replace('/\s+/',' ',preg_replace('/\r\n/',' ',strip_tags($campaign->html)));
        $campaign->save();

        return redirect()->action('CampaignController@templatePreview', ['uid' => $campaign->uid]);
    }

    public function listSegmentForm(Request $request)
    {
        // Get current user
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            /** @var Customer $selected_customer */
            $selected_customer = $request->selected_customer;
            $mailListSelectOptions = $selected_customer->getMailListSelectOptions([], false);
            foreach ($mailListSelectOptions as &$list) {
                $mailingList = MailList::findByUid($list['value']);
                $list['segments'] = $this->selectOptionsWithLabel($mailingList->getSegmentSelectOptions(false));
            }

            return response()->json([
                //'campaign' => $campaign,
                'mail_list_select_options' => $this->selectOptionsWithLabel($mailListSelectOptions),
            ]);
        }
        return view('campaigns._list_segment_form', [
            'campaign' => $campaign,
            'lists_segment_group' => [
                'list' => null,
                'is_default' => false,
            ],
        ]);
    }

    /**
     * Email web view.
     */
    public function webView(Request $request)
    {
        $message_id = StringHelper::base64UrlDecode($request->message_id);
        $tracking_log = TrackingLog::where('message_id', '=', $message_id)->first();

        try {
            if (!is_object($tracking_log)) {
                throw new Exception(trans('messages.web_view_can_not_find_tracking_log_with_message_id'));
            }

            $subscriber = $tracking_log->subscriber;
            $campaign = $tracking_log->campaign;

            if (!is_object($campaign) || !is_object($subscriber)) {
                throw new Exception(trans('messages.web_view_can_not_find_campaign_or_subscriber'));
            }

            return view('campaigns.web_view', [
                'campaign' => $campaign,
                'subscriber' => $subscriber,
                'message_id' => $message_id,
            ]);
        } catch (Exception $e) {
            MailLog::error($e->getMessage());

            return view('somethingWentWrong', ['message' => trans('messages.the_email_no_longer_exists')]);
        }
    }

    /*
     * Select campaign type page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function selectType(Request $request)
    {
        // authorize
        if (Gate::denies('create', new Campaign())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.select_type');
    }

    /**
     * Template review.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateReview(Request $request)
    {
        // Get current user
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.template_review', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Template review iframe.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function templateReviewIframe(Request $request)
    {
        // Get current user
        $campaign = Campaign::findByUid($request->uid);

        // authorize
        if (Gate::denies('read', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        return view('campaigns.template_review_iframe', [
            'campaign' => $campaign,
        ]);
    }

    public function resend(Request $request, $uid)
    {
        $customer = $request->selected_customer;
        $campaign = Campaign::findByUid($uid);

        // do resend with option: $request->option : not_receive|not_open|not_click
        if ($request->isMethod('post')) {
            // authorize
            if (Gate::allows('resend', $campaign)) {
                $campaign->ready();
                $data = $request->validate([
                    'option' => 'required|string|in:not_receive,not_open,not_click,all'
                ]);
                $campaign->resend($data['option']);
                // Redirect to my lists page
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.campaign.resent'),
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => trans('messages.not_authorized_message'),
                ]);
            }
        }
        if ($request->expectsJson()) {
            return response()->json([
                'options' => [
                    ['text' => trans('messages.campaign.resend.option.not_receive'), 'value' => 'not_receive'],
                    ['text' => trans('messages.campaign.resend.option.not_open'), 'value' => 'not_open'],
                    ['text' => trans('messages.campaign.resend.option.not_click'), 'value' => 'not_click'],
                    ['text' => "All", 'value' => 'all'],
                ]
            ]);
        }

        return view('campaigns.resend', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Get client IP address.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRemoteIPAddress()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ip = preg_split('/\s*,\s*/', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            return $ip;
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Get spam score.
     *
     * @param Request $request
     *
     * @return string
     */
    public function spamScore(Request $request, $uid)
    {
        // Get current user
        $campaign = Campaign::findByUid($uid);

        try {
            $score = $campaign->score();
        } catch (Exception $e) {
            return response()->json("Cannot get score. Make sure you setup for SpamAssassin correctly.\r\n" . $e->getMessage(), 500); // Status code here
        }

        return view('campaigns.spam_score', [
            'score' => $score,
        ]);
    }

    /**
     * Change template from exist template.
     *
     */
    public function templateChangeTemplate(Request $request, $uid, $template_uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);
        $changeTemplate = Template::findByUid($template_uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        $campaign->changeTemplate($changeTemplate);
    }

    /**
     * Edit email content.
     *
     */
    public function builderClassic(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'html' => 'required',
            );

            // make validator
            $validator = Validator::make($request->all(), $rules);

            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }

            // Save template
            $campaign->html = $request->html;
            $campaign->preheader = $request->preheader;
            $campaign->untransform();
            $campaign->save();

            // update plain
            $campaign->updatePlainFromHtml();

            // success
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.updated'),
            ], 201);
        }

        return view('campaigns.builderClassic', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Edit plain text.
     *
     */
    public function builderPlainEdit(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'plain' => 'required',
            );

            // make validator
            $validator = Validator::make($request->all(), $rules);

            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }

            // Save template
            $campaign->plain = $request->plain;
            $campaign->untransform();
            $campaign->save();

            // success
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.updated'),
            ], 201);
        }

        return view('campaigns.builderPlainEdit', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Upload attachment.
     *
     */
    public function uploadAttachment(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        if (is_array($request->file))
            foreach ($request->file as $file) {
                $campaign->uploadAttachment($file);
            }
    }

    /**
     * Download attachment.
     *
     */
    public function downloadAttachment(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        return response()->download($campaign->getAttachmentPath($request->name), $request->name);
    }

    public function removeAttachment(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        unlink($campaign->getAttachmentPath($request->name));
    }

    public function getAttachments(Request $request, $uid)
    {
        // Generate info
        $campaign = Campaign::findByUid($uid);

        // authorize
        if (!$request->selected_customer->can('update', $campaign)) {
            return $this->notAuthorized();
        }

        $results = [];
        foreach ($campaign->getAttachments() as $k => $filename) {
            $results[] = [
                "name" => $filename,
                "size" => formatSizeUnits(filesize($campaign->getAttachmentPath($filename))),
            ];
        }

        return response()->json(["attachments" => $results]);

    }

    public function run(Request $request, $uid)
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::findByUid($uid);
        $campaign->start();
        echo $campaign->status;
    }

    public function tags(Request $request, $uid)
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::findByUid($uid);
        $tags = Template::builderTags($campaign ? $campaign->defaultMailList : null);
        return view('ew_tags', ['tags' => $tags]);
    }
}
