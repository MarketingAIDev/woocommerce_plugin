<?php

namespace Acelle\Http\Controllers;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Library\Automation\Evaluate;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Model\DebugLog;
use Acelle\Model\Segment2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Acelle\Model\Automation2;
use Acelle\Model\MailList;
use Acelle\Model\Email;
use Acelle\Model\Attachment;
use Acelle\Model\Template;
use Acelle\Model\Subscriber;
use Illuminate\Validation\Rule;

class Automation2Controller extends Controller
{

    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $automations = $request->selected_customer->automation2s();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.index',
                'automations' => $automations,
            ]);
        }
        return view('automation2.index', [
            'automations' => $automations,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function listing(Request $request)
    {
        $automations = Automation2::search($request)->paginate($request->per_page);

        if ($request->wantsJson()) {
            foreach ($automations as $automation) {
                $list = $automation->mailList;
                $automation->_briefIntro = $automation->getBriefIntro();
                $automation->_countEmails = $automation->countEmails();
                $automation->_summaryStats = $automation->getSummaryStats();
                $automation->_totalEmailCount = $automation->email_actions()->count();
            }
            return response()->json([
                'view' => 'automation2._list',
                'automations' => $automations,
            ]);
        }
        return view('automation2._list', [
            'automations' => $automations,
        ]);
    }

    /**
     * Creating a new resource.
     *
     */
    public function create(Request $request)
    {
        $customer = $request->selected_customer;

        // init automation
        $automation = new Automation2([
            'name' => trans('messages.automation.untitled'),
        ]);
        $automation->status = Automation2::STATUS_INACTIVE;

        // authorize
        if (Gate::denies('create', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'noMoreItem'
                ]);
            }
            return $this->noMoreItem();
        }

        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $automation->fillRequest($request);

            // make validator
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'mail_list_uid' => ['required', 'string', Rule::exists('mail_lists', 'uid')->where(MailList::COLUMN_customer_id, $customer->id)],
                'segment2_id' => ['nullable', 'integer', Rule::exists('segment2s', 'id')->where(Segment2::COLUMN_customer_id, $customer->id)],
            ]);

            // redirect if fails
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.create',
                        'automation' => $automation,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                return response()->view('automation2.create', [
                    'automation' => $automation,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // pass validation and save
            $automation->mail_list_id = MailList::findByUid($request->mail_list_uid)->id;
            $automation->segment2_id = $request->segment2_id ?: null;
            $automation->customer_id = $customer->id;
            $automation->data = '[{"title":"Click to choose a trigger","id":"trigger","type":"ElementTrigger","options":{"init":"false", "key": ""}}]';
            $automation->save();

            DebugLog::automation2Log("created", $automation->uid . ' ' . $automation->name, []);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.created.redirecting'),
                'uid' => $automation->uid,
                'url' => action('Automation2Controller@edit', ['uid' => $automation->uid])
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.create',
                'automation' => $automation,
            ]);
        }
        return view('automation2.create', [
            'automation' => $automation,
        ]);
    }

    /**
     * Update automation.
     *
     *
     */
    public function update(Request $request, $uid)
    {
        $customer = $request->selected_customer;

        // find automation
        /** @var Automation2 $automation */
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // fill before save
        $automation->fillRequest($request);

        // make validator
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'segment2_id' => ['nullable', 'integer', Rule::exists('segment2s', 'id')->where(Segment2::COLUMN_customer_id, $customer->id)],
        ]);

        // redirect if fails
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'automation2.settings',
                    'automation' => $automation,
                    'errors' => $validator->errors(),
                ], 400);
            }
            return response()->view('automation2.settings', [
                'automation' => $automation,
                'errors' => $validator->errors(),
            ], 400);
        }

        // pass validation and save
        // $automation->updateMailList(MailList::findByUid($request->mail_list_uid));

        // save
        $automation->segment2_id = $request->segment2_id ?: null;
        $automation->save();
        DebugLog::automation2Log("updated", $automation->uid . ' ' . $automation->name, []);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.updated'),
        ], 201);
    }

    /**
     * Update automation.
     */
    public function saveData(Request $request, $uid)
    {
        // find automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $automation->saveData($request->data);
    }

    /**
     * Creating a new resource.
     *
     *
     */
    public function edit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }
        $automation->updateCacheInBackground();

        $this->setReferrer(self::class);
        $backURL = $this->getReferrer(self::class, action([self::class, 'index']));
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.edit',
                'automation' => $automation,
                'backURL' => $backURL
            ]);
        }
        return view('automation2.edit', [
            'automation' => $automation,
            'backURL' => $backURL
        ]);
    }

    /**
     * Automation settings in sidebar.
     *
     *
     */
    public function settings(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $this->setReferrer(self::class);
        $backURL = $this->getReferrer(self::class, action([self::class, 'index']));
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.settings',
                'automation' => $automation,
                'backURL' => $backURL
            ]);
        }
        return view('automation2.settings', [
            'automation' => $automation,
            'backURL' => $backURL
        ]);
    }

    /**
     * Select trigger type popup.
     *
     *
     */
    public function triggerSelectPupop(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $types = [
            'welcome-new-subscriber',
            'say-happy-birthday',
            'subscriber-added-date',
            'specific-date',
            'say-goodbye-subscriber',
            'api-3-0',
            'weekly-recurring',
            'monthly-recurring',
        ];
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.triggerSelectPupop',
                'automation' => $automation,
                'trigger' => $automation->getTrigger(),
            ]);
        }
        return view('automation2.triggerSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }

    /**
     * Select trigger type confirm.
     *
     *
     */
    public function triggerSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->key];

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.triggerSelectConfirm',
                'key' => $request->key,
                'automation' => $automation,
                'trigger' => $automation->getTrigger(),
                'rules' => $rules,
            ]);
        }
        return view('automation2.triggerSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }

    /**
     * Select trigger type.
     *
     * @return array
     */
    public function triggerRules()
    {
        $original_rules = [
            'welcome-new-subscriber' => [],
            'say-happy-birthday' => [
                'options.before' => 'required',
                'options.at' => 'required',
                'options.field' => 'required',
            ],
            'specific-date' => [
                'options.date' => 'required',
                'options.at' => 'required',
            ],
            'say-goodbye-subscriber' => [],
            'api-3-0' => [],
            'subscriber-added-date' => [
                'options.delay' => 'required',
                'options.at' => 'required',
            ],
            'weekly-recurring' => [
                'options.days_of_week' => 'required',
                'options.at' => 'required',
            ],
            'monthly-recurring' => [
                'options.days_of_month' => 'required|array|min:1',
                'options.at' => 'required',
            ],
        ];

        $automation_triggers = [];
        foreach (ShopifyAutomationContext::AUTOMATION_TRIGGERS as $trigger)
            $automation_triggers[$trigger] = [];
        $automation_triggers[ShopifyAutomationContext::AUTOMATION_TRIGGER_POPUP_SUBMITTED] = [
            'options.popup_uid' => 'required|string'
        ];
        return array_merge($original_rules, $automation_triggers);
    }

    /**
     * Select trigger type.
     *
     *
     */
    public function triggerSelect(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->options['key']];

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // make validator
        $validator = Validator::make($request->all(), $rules);

        // redirect if fails
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'automation2.triggerSelectConfirm',
                    'key' => $request->options['key'],
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'rules' => $rules,
                    'errors' => $validator->errors(),
                ], 400);
            }
            return response()->view('automation2.triggerSelectConfirm', [
                'key' => $request->options['key'],
                'automation' => $automation,
                'trigger' => $automation->getTrigger(),
                'rules' => $rules,
                'errors' => $validator->errors(),
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.trigger.added'),
            'title' => trans('messages.automation.trigger.title', [
                'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
            ]),
            'options' => $request->options,
            'rules' => $rules,
        ]);
    }

    /**
     * Select action type popup.
     *
     *
     */
    public function actionSelectPupop(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $types = [
            'send-an-email',
            'wait',
            'condition',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.actionSelectPupop',
                'types' => $types,
                'automation' => $automation,
                'hasChildren' => $request->hasChildren,
            ]);
        }
        return view('automation2.actionSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'hasChildren' => $request->hasChildren,
        ]);
    }

    /**
     * Select action type confirm.
     *
     *
     */
    public function actionSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.actionSelectConfirm',
                'key' => $request->key,
                'automation' => $automation,
                'element' => $automation->getElement(),
            ]);
        }
        return view('automation2.actionSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement(),
        ]);
    }

    /**
     * Select trigger type.
     *
     *
     */
    public function actionSelect(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        switch ($request->key) {
            case "wait":
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.wait.delay.' . $request->time),
                    'options' => [
                        'key' => $request->key,
                        'time' => $request->time,
                    ],
                ]);
            case "condition":
                switch ($request->type) {
                    case Evaluate::CONDITION_open:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition.read_email.title'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'email' => empty($request->email) ? null : $request->email,
                            ],
                        ]);
                    case Evaluate::CONDITION_click:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition.click_link.title'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'email_link' => empty($request->email_link) ? null : $request->email_link,
                            ],
                        ]);
                    case Evaluate::CONDITION_checkout_abandoned:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.checkout_abandoned'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                            ]
                        ]);
                    case Evaluate::CONDITION_review_image:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.review_image'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                            ]
                        ]);
                    case Evaluate::CONDITION_discount_coupon_used:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.discount_coupon_used'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'discount_coupon' => $request->discount_coupon ?? null
                            ]
                        ]);
                    case Evaluate::CONDITION_review_stars:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.review_stars'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'review_stars_gte' => empty($request->review_stars_gte) ? null : $request->review_stars_gte,
                                'review_stars_lte' => empty($request->review_stars_lte) ? null : $request->review_stars_lte,
                            ]
                        ]);
                    case Evaluate::CONDITION_review_submitted:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.review_submitted'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'review_products_type' => $request->review_products_type == "all" ? "all" : "any",
                            ]
                        ]);
                    case Evaluate::CONDITION_chat_stars:
                        return response()->json([
                            'status' => 'success',
                            'message' => trans('messages.automation.action.added'),
                            'title' => trans('messages.automation.action.condition_title.chat_stars'),
                            'options' => [
                                'key' => $request->key,
                                'type' => $request->type,
                                'chat_stars_gte' => empty($request->chat_stars_gte) ? null : $request->chat_stars_gte,
                                'chat_stars_lte' => empty($request->chat_stars_lte) ? null : $request->chat_stars_lte,
                            ]
                        ]);
                }
            default:
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.action.title', [
                        'title' => trans('messages.automation.action.' . $request->key)
                    ]),
                    'options' => [
                        'key' => $request->key,
                        'after' => $request->after,
                    ],
                ]);
        }
    }

    /**
     * Edit trigger.
     *
     *
     */
    public function triggerEdit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->key];

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), $this->triggerRules()[$request->options['key']]);
            $rules = $this->triggerRules()[$request->options['key']];

            // redirect if fails
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.triggerEdit',
                        'key' => $request->options['key'],
                        'automation' => $automation,
                        'trigger' => $automation->getTrigger(),
                        'rules' => $rules,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                return response()->view('automation2.triggerEdit', [
                    'key' => $request->options['key'],
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'rules' => $rules,
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.trigger.updated'),
                'title' => trans('messages.automation.trigger.title', [
                    'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
                ]),
                'options' => $request->options,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.triggerEdit',
                'key' => $request->key,
                'automation' => $automation,
                'trigger' => $automation->getTrigger(),
                'rules' => $rules,
            ]);
        }
        return view('automation2.triggerEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }

    /**
     * Edit action.
     *
     *
     */
    public function actionEdit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            switch ($request->key) {
                case "wait":
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.added'),
                        'title' => trans('messages.automation.wait.delay.' . $request->time),
                        'options' => [
                            'key' => $request->key,
                            'time' => $request->time,
                        ],
                    ]);
                case "condition":
                    switch ($request->type) {
                        case Evaluate::CONDITION_open:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition.read_email.title'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'email' => empty($request->email) ? null : $request->email,
                                ],
                            ]);
                        case Evaluate::CONDITION_click:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition.click_link.title'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'email_link' => empty($request->email_link) ? null : $request->email_link,
                                ],
                            ]);
                        case Evaluate::CONDITION_checkout_abandoned:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.checkout_abandoned'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                ]
                            ]);
                        case Evaluate::CONDITION_review_image:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.review_image'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                ]
                            ]);
                        case Evaluate::CONDITION_discount_coupon_used:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.discount_coupon_used'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'discount_coupon' => $request->discount_coupon ?? null
                                ]
                            ]);
                        case Evaluate::CONDITION_review_stars:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.review_stars'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'review_stars_gte' => empty($request->review_stars_gte) ? null : $request->review_stars_gte,
                                    'review_stars_lte' => empty($request->review_stars_lte) ? null : $request->review_stars_lte,
                                ]
                            ]);
                        case Evaluate::CONDITION_review_submitted:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.review_submitted'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'review_products_type' => $request->review_products_type == "all" ? "all" : "any",
                                ]
                            ]);
                        case Evaluate::CONDITION_chat_stars:
                            return response()->json([
                                'status' => 'success',
                                'message' => trans('messages.automation.action.added'),
                                'title' => trans('messages.automation.action.condition_title.chat_stars'),
                                'options' => [
                                    'key' => $request->key,
                                    'type' => $request->type,
                                    'chat_stars_gte' => empty($request->chat_stars_gte) ? null : $request->chat_stars_gte,
                                    'chat_stars_lte' => empty($request->chat_stars_lte) ? null : $request->chat_stars_lte,
                                ]
                            ]);
                    }
                default:
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.added'),
                        'title' => trans('messages.automation.action.title', [
                            'title' => trans('messages.automation.action.' . $request->key)
                        ]),
                        'options' => [
                            'key' => $request->key,
                            'after' => $request->after,
                        ],
                    ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.actionEdit',
                'key' => $request->key,
                'automation' => $automation,
                'element' => $automation->getElement($request->id)
            ]);
        }
        return view('automation2.actionEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement($request->id),
        ]);
    }

    /**
     * Email setup.
     *
     *
     */
    public function emailSetup(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        if ($request->email_uid) {
            $email = Email::findByUid($request->email_uid);
        } else {
            $email = new Email([
                'sign_dkim' => true,
                'track_open' => true,
                'track_click' => true,
                'action_id' => $request->action_id,
            ]);
        }

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $email->fillAttributes($request->all());

            // Tacking domain
            if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
                $tracking_domain = \Acelle\Model\TrackingDomain::findByUid($params['tracking_domain_uid']);
                if (is_object($tracking_domain)) {
                    $this->tracking_domain_id = $tracking_domain->id;
                } else {
                    $this->tracking_domain_id = null;
                }
            } else {
                $this->tracking_domain_id = null;
            }

            // make validator
            $validator = Validator::make($request->all(), $email->rules($request));

            // redirect if fails
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.email.setup',
                        'automation' => $automation,
                        'email' => $email,
                        'errors' => $validator->errors(),
                    ], 400);
                }

                return response()->view('automation2.email.setup', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // pass validation and save
            $email->automation2_id = $automation->id;
            $email->save();

            return response()->json([
                'status' => 'success',
                'title' => trans('messages.automation.send_a_email', ['title' => $email->subject]),
                'message' => trans('messages.automation.email.set_up.success'),
                'url' => action('Automation2Controller@emailTemplate', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]),
                'options' => [
                    'email_uid' => $email->uid,
                ],
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.setup',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.setup', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Delete automation email.
     *
     *
     */
    public function emailDelete(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // delete email
        $email->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.deteled'),
        ], 201);
    }

    /**
     * Email template.
     *
     *
     */
    public function emailTemplate(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if (!$email->hasTemplate()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'Automation2Controller@templateCreate',
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]);
            }
            return redirect()->action('Automation2Controller@templateCreate', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.template', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Email show.
     *
     *
     */
    public function email(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.index',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.index', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Email confirm.
     *
     *
     */
    public function emailConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.confirm',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.confirm', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Create template.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateCreate(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.create',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.template.create', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Create template from layout.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateLayout(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // add layout to campaign template
        if ($request->isMethod('post')) {
            if ($request->layout) {
                $email->addTemplateFromLayout($request->layout);

                // update email plain text
                $email->updatePlainFromContent();

                // update links
                $email->updateLinks();
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.layout.selected'),
                'url' => action('Automation2Controller@templateBuilderSelect', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]),
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.layout',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.template.layout', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Select builder for editing template.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateBuilderSelect(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.email.template.templateBuilderSelect', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Edit campaign template.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateEdit(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
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

            $email->content = $request->post('content');
            //$email->untransform();
            //$email->saveImages();
            $email->save();

            // update email plain text
            $email->updatePlainFromContent();

            // update links
            $email->updateLinks();

            return response()->json([
                'status' => 'success',
            ]);
        }
        return view('automation2.email.contentbuilder', [
            'automation' => $automation,
            'list' => $automation->mailList,
            'email' => $email,
            'tags' => $automation->getAutomationTags(),
            'templates' => $email->getBuilderTemplates($request->selected_customer),
        ]);
    }

    /**
     * Upload asset to builder.
     *
     * @param int $id
     *
     *
     */
    public function templateAsset(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $filename = $email->uploadAsset($request->file('file'));

        return response()->json([
            'url' => route('customer_files', ['uid' => $request->user()->uid, 'name' => $filename])
        ]);
    }

    /**
     * Campaign html content.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateContent(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.content',
                'content' => $email->render(),
            ]);
        }
        return view('automation2.email.template.content', [
            'content' => $email->render(),
        ]);
    }

    /**
     * Create template from theme.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateTheme(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            $template = Template::findByUid($request->template_uid);
            $email->copyFromTemplate($template);

            // update email plain text
            $email->updatePlainFromContent();

            // update links
            $email->updateLinks();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.theme.selected'),
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.theme',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.template.theme', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     *
     */
    public function templateThemeList(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->selected_customer->id));
        list($templates, $pagination) = pagination($request, Template::search($request));

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.themeList',
                'automation' => $automation,
                'email' => $email,
                'templates' => $templates,
                'pagination' => $pagination,
            ]);
        }
        return view('automation2.email.template.themeList', [
            'automation' => $automation,
            'email' => $email,
            'templates' => $templates,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized',
                ]);
            }
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            list($result, $validator) = $email->uploadTemplate($request);

            if (!$result) {
                // update email plain text
                $email->updatePlainFromContent();

                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.email.template.upload',
                        'automation' => $automation,
                        'email' => $email,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                return response()->view('automation2.email.template.upload', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            } else {
                // update links
                $email->updateLinks();

                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.email.template.uploaded'),
                ], 201);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.email.template.upload',
                'automation' => $automation,
                'email' => $email,
            ]);
        }
        return view('automation2.email.template.upload', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Remove exist template.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateRemove(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $email->removeTemplate();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.template.removed'),
        ], 201);
    }

    /**
     * Template preview.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templatePreview(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'campaigns.template_preview',
                'automation' => $automation,
                //'rules' => $rules,
            ]);
        }

        return view('automation2.email.template.preview', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Attachment upload.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function emailAttachmentUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $request->validate([
            'file' => 'required|array',
            'file.*' => 'required|file',
        ]);

        foreach ($request->file as $file) {
            $email->uploadAttachment($file);
        }
    }

    /**
     * Attachment remove.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function emailAttachmentRemove(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $attachment = Attachment::findByUid($request->attachment_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $attachment->remove();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.attachment.removed'),
        ], 201);
    }

    /**
     * Attachment download.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function emailAttachmentDownload(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        $attachment = Attachment::findByUid($request->attachment_uid);

        // authorize
        if (Gate::denies('read', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        return response()->download(storage_path('app/' . $attachment->file), $attachment->name);
    }

    /**
     * Enable automation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     *
     */
    public function enable(Request $request)
    {
        /** @var Automation2[] $automations */
        $automations = Automation2::whereIn('uid', explode(',', $request->uids))->get();

        $errors = [];
        foreach ($automations as $automation) {
            // authorize
            if (Gate::denies('enable', $automation)) {
                $errors[] = "You are not authorized to enable automation: " . $automation->name;
                continue;
            }
            if (!$automation->getTriggerAction() || !$automation->getTriggerAction()->getOption('key')) {
                $errors[] = "Trigger is not set for automation: " . $automation->name;
                continue;
            }

            $automation->enable();
        }

        if (count($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => implode(', ', $errors)
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.enabled', $automations->count()),
        ]);
    }

    /**
     * Disable event.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     *
     */
    public function disable(Request $request)
    {
        $automations = Automation2::whereIn('uid', explode(',', $request->uids));

        foreach ($automations->get() as $automation) {
            // authorize
            if (Gate::allows('disable', $automation)) {
                $automation->disable();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.disabled', $automations->count()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json([
                'status' => 'notice',
                'message' => trans('messages.operation_not_allowed_in_demo'),
            ]);
        }

        $automations = Automation2::whereIn('uid', explode(',', $request->uids));

        foreach ($automations->get() as $automation) {
            // authorize
            if (Gate::allows('delete', $automation)) {
                $automation->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.deleted', $automations->count()),
        ]);
    }

    /**
     * Automation insight page.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function insight(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.insight',
                'automation' => $automation,
                'stats' => $automation->getSummaryStats(),
                'insight' => $automation->getInsight(),
            ]);
        }
        return view('automation2.insight', [
            'automation' => $automation,
            'stats' => $automation->readCache('SummaryStats'),
            'insight' => $automation->getInsight(),
        ]);
    }

    /**
     * Automation contacts list.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function contacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // all or action contacts
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.contacts.index',
                'automation' => $automation,
                'subscribers' => $subscribers,
            ]);
        }
        return view('automation2.contacts.index', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function contactsList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // all or action contacts
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        list($contacts, $pagination) = pagination($request, $subscribers);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.contacts.list',
                'automation' => $automation,
                'contacts' => $contacts,
                'pagination' => $pagination,
            ]);
        }
        return view('automation2.contacts.list', [
            'automation' => $automation,
            'contacts' => $contacts,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Automation timeline.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function timeline(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.timeline.index',
                'automation' => $automation,
            ]);
        }
        return view('automation2.timeline.index', [
            'automation' => $automation,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function timelineList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        list($timelines, $pagination) = pagination($request, $automation->timelines());

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.timeline.list',
                'automation' => $automation,
                'timelines' => $timelines,
                'pagination' => $pagination,
            ]);
        }
        return view('automation2.timeline.list', [
            'automation' => $automation,
            'timelines' => $timelines,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Automation contact profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function profile(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.profile',
                'automation' => $automation,
                'contact' => $contact,
            ]);
        }
        return view('automation2.profile', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation remove contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function removeContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.deleted'),
        ], 201);
    }

    /**
     * Automation tag contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function tagContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $contact->updateTags($request->tags);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contact.tagged', [
                    'contact' => $contact->getFullName(),
                ]),
            ], 201);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.contacts.tagContact',
                'automation' => $automation,
                'contact' => $contact,
            ]);
        }

        return view('automation2.contacts.tagContact', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation tag contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function tagContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'tags' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.contacts.tagContacts',
                        'automation' => $automation,
                        'subscribers' => $subscribers,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                return response()->view('automation2.contacts.tagContacts', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->addTags($request->tags);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.tagged', [
                    'count' => $subscribers->count(),
                ]),
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.contacts.tagContacts',
                'automation' => $automation,
                'subscribers' => $subscribers,
            ]);
        }
        return view('automation2.contacts.tagContacts', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation remove contact tag.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function removeTag(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $contact->removeTag($request->tag);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.tag.removed', [
                'tag' => $request->tag,
            ]),
        ], 201);
    }

    /**
     * Automation export contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function exportContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.exported'),
            ], 201);
        }
    }

    /**
     * Automation copy contacts to new list.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function copyToNewList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'view' => 'automation2.contacts.copyToNewList',
                        'automation' => $automation,
                        'subscribers' => $subscribers,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                return response()->view('automation2.contacts.copyToNewList', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Crate new list
            $list = $automation->mailList->copy($request->name);

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->copy($list);
            }

            // update cache
            $list->updateCache();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.copied_to_new_list', [
                    'count' => $subscribers->count(),
                    'list' => $list->name,
                ]),
            ], 201);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'automation2.contacts.copyToNewList',
                'automation' => $automation,
                'subscribers' => $subscribers,
            ]);
        }
        return view('automation2.contacts.copyToNewList', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateEditClassic(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $this->validate($request, $rules);

            $email->content = $request->post('content');
            $email->untransform();
            $email->save();

            // update email plain text
            $email->updatePlainFromContent();

            // update links
            $email->updateLinks();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.content.updated'),
            ], 201);
        }

        return view('automation2.email.template.editClassic', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     *
     */
    public function templateEditPlain(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'plain' => 'required',
            );

            // make validator
            $validator = Validator::make($request->all(), $rules);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.email.template.editPlain', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }

            $email->plain = $request->plain;
            $email->untransform();
            $email->save();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.plain.updated'),
            ], 201);
        }

        return view('automation2.email.template.editPlain', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Segment select.
     *
     *
     */
    public function segmentSelect(Request $request)
    {
        if (!$request->list_uid) {
            return '';
        }

        // init automation
        if ($request->uid) {
            $automation = Automation2::findByUid($request->uid);

            // authorize
            if (Gate::denies('view', $automation)) {
                return $this->notAuthorized();
            }
        } else {
            $automation = new Automation2();

            // authorize
            if (Gate::denies('create', $automation)) {
                return $this->notAuthorized();
            }
        }
        $list = MailList::findByUid($request->list_uid);

        return view('automation2.segmentSelect', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of subscribers.
     *
     *
     */
    public function subscribers(Request $request, $uid)
    {
        // init
        $automation = Automation2::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.subscribers.index', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     *
     */
    public function subscribersList(Request $request, $uid)
    {
        // init
        $automation = Automation2::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        if (Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $subscribers = $automation->subscribers()->search($request)
            ->where('mail_list_id', '=', $list->id);

        // $total = distinctCount($subscribers);
        $total = $subscribers->count();
        $subscribers->with(['mailList', 'subscriberFields']);
        $subscribers = \optimized_paginate($subscribers, $request->per_page, null, null, null, $total);

        $fields = $list->getFields->whereIn('uid', explode(',', $request->columns));

        return view('automation2.subscribers._list', [
            'automation' => $automation,
            'subscribers' => $subscribers,
            'total' => $total,
            'list' => $list,
            'fields' => $fields,
        ]);
    }

    /**
     * Remove subscriber from automation.
     *
     *
     */
    public function subscribersRemove(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.removed'),
        ], 201);
    }

    /**
     * Restart subscriber for automation.
     *
     *
     */
    public function subscribersRestart(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.restarted'),
        ], 201);
    }

    /**
     * Display a listing of subscribers.
     *
     *
     */
    public function subscribersShow(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (Gate::denies('read', $subscriber)) {
            return;
        }

        return view('automation2.subscribers.show', [
            'automation' => $automation,
            'subscriber' => $subscriber,
        ]);
    }

    public function lastSaved($uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }
        return response("Last saved: " . $automation->updated_at->diffForHumans());
    }

    public function tags(Request $request, $uid)
    {
        /** @var Automation2 $automation */
        $automation2 = Automation2::findByUid($uid);
        if (!$automation2)
            return response("", 404);
        return view('ew_tags', ['tags' => $automation2->getAutomationTags()]);
    }

    public function copy(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        // authorize
        if (Gate::denies('copy', $automation)) {
            return $this->notAuthorized();
        }
        $automation->makeCopy($request->get('copy_automation_name', $automation->name), $request->selected_customer);
        return response('Copied');
    }

    function public_automations(Request $request): JsonResponse
    {
        $automations = Automation2::query()->where(Automation2::COLUMN_default_for_new_customers, 1)->paginate($request->per_page);
        return response()->json([
            'public_automations' => $automations
        ]);
    }

    function update_public_flag(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        if (Gate::denies('update', $automation))
            return $this->notAuthorized();

        $user = $request->user();
        if (!$user || !($user->admin))
            return $this->notAuthorized();

        $data = custom_validate($request->all(), [
            Automation2::COLUMN_default_for_new_customers => 'required|bool'
        ]);
        $automation->default_for_new_customers = (bool)($data[Automation2::COLUMN_default_for_new_customers] ?? false);
        $automation->save();
        return response('Updated');
    }
}
