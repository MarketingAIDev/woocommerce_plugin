<?php

/**
 * Automation class.
 *
 * Model for automations
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Jobs\UpdateAutomation;
use Acelle\Library\Automation\Action;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Library\Automation\Evaluate;
use Acelle\Library\Automation\Operate;
use Acelle\Library\Automation\Send;
use Acelle\Library\Automation\Trigger;
use Acelle\Library\Automation\Wait;
use Acelle\Library\Lockable;
use Acelle\Model\SystemJob as SystemJobModel;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use function Clue\StreamFilter\fun;
use Acelle\Model\DebugLog;

/**
 * Class Automation2
 * @package Acelle\Model
 * @property integer id
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property string uid
 * @property string name
 * @property integer customer_id
 * @property Customer customer
 * @property integer mail_list_id
 * @property MailList mailList
 * @property string time_zone
 * @property string status
 * @property string data
 * @property string segment_id
 * @property integer segment2_id
 * @property string cache
 * @property string last_error
 * @property string trigger_key
 * @property bool default_for_new_customers
 *
 * @property AutoTrigger[] autoTriggers
 * @property Email[] emails
 * @property EmailLink[] emailLinks
 * @property AutoTrigger[] pendingAutoTriggers
 * @property Timeline[] timelines
 *
 * @method static static|null find(integer $id)
 */
class Automation2 extends Model
{
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_uid = 'uid';
    const COLUMN_name = 'name';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_mail_list_id = 'mail_list_id';
    const COLUMN_time_zone = 'time_zone';
    const COLUMN_status = 'status';
    const COLUMN_data = 'data';
    const COLUMN_segment_id = 'segment_id';
    const COLUMN_segment2_id = 'segment2_id';
    const COLUMN_cache = 'cache';
    const COLUMN_last_error = 'last_error';
    const COLUMN_trigger_key = 'trigger_key';
    const COLUMN_default_for_new_customers = 'default_for_new_customers';

    // Automation status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const SORTABLE_FIELDS = [
        'id',
        self::COLUMN_created_at,
        self::COLUMN_updated_at,
        self::COLUMN_name,
        self::COLUMN_status,
    ];

    /**
     * Items per page.
     *
     * @var array
     */
    const ITEMS_PER_PAGE = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        self::COLUMN_default_for_new_customers => "bool"
    ];

    // region Relations

    public function mailList(): BelongsTo
    {
        return $this->belongsTo(MailList::class);
    }

    public function emailLinks(): HasManyThrough
    {
        return $this->hasManyThrough(EmailLink::class, Email::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function autoTriggers(): HasMany
    {
        return $this->hasMany(AutoTrigger::class);
    }

    public function pendingAutoTriggers()
    {
        $leaves = $this->getLeafActions();
        $condition = '(' . implode(' AND ', array_map(function ($e) {
                return "executed_index NOT LIKE '%{$e}'";
            }, $leaves)) . ')';
        $query = $this->autoTriggers()->whereRaw($condition);
        return $query;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class)->orderBy('created_at', 'DESC');
    }

    public function email_actions(): HasMany
    {
        return $this->hasMany(Timeline::class)->where('activity_type', 'ElementAction');
    }

    // endregion

    function getAutomationTags(): array
    {
        $tags = Template::builderTags($this->mailList);
        switch ($this->trigger_key) {
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_SUBMITTED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_UPDATED:
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{REVIEW_EDIT_LINK}',
                    'name' => 'REVIEW_EDIT_LINK',
                    'required' => true,
                ];
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_LINK}',
                    'name' => 'PRODUCT_LINK',
                    'required' => true,
                ];
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_TITLE}',
                    'name' => 'PRODUCT_TITLE',
                    'required' => true,
                ];
                break;
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED:
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_LINK}',
                    'name' => 'PRODUCT_LINK',
                    'required' => true,
                ];
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_TITLE}',
                    'name' => 'PRODUCT_TITLE',
                    'required' => true,
                ];
                break;
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_CHECKOUT_ABANDONED:
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{ABANDONED_CHECKOUT_LINK}',
                    'name' => 'ABANDONED_CHECKOUT_LINK',
                    'required' => true,
                ];
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_LINK}',
                    'name' => 'PRODUCT_LINK',
                    'required' => true,
                ];
                $tags[] = [
                    'type' => 'label',
                    'text' => '',
                    'tag' => '{PRODUCT_TITLE}',
                    'name' => 'PRODUCT_TITLE',
                    'required' => true,
                ];
                break;
        }
        return $tags;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function (self $item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
            $item->trigger_key = $item->getTriggerAction()->getOption('key');
        });

        static::updating(function (self $item) {
            $item->trigger_key = $item->getTriggerAction()->getOption('key');
        });
    }

    /**
     * Find item by uid.
     *
     * @return static
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * @param string $trigger
     * @param boolean $active
     * @return self[]
     */
    static function findByTrigger(Customer $customer, string $trigger, $active = true)
    {
        $query = self::where(self::COLUMN_trigger_key, $trigger)
            ->where(self::COLUMN_customer_id, $customer->id);
        if ($active) {
            $query->where(self::COLUMN_status, self::STATUS_ACTIVE);
        }
        return $query->get();
    }

    /**
     * @param string $trigger
     * @param bool $active
     * @return self[]
     */
    static function findByTriggerOnly(string $trigger, $active = true)
    {
        $query = self::where(self::COLUMN_trigger_key, $trigger);
        if ($active) {
            $query->where(self::COLUMN_status, self::STATUS_ACTIVE);
        }
        return $query->get();
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'mail_list_uid' => 'required|string',
            'segment2_id' => 'nullable|integer',
        ];
    }

    public static function filter($request)
    {
        //$user = $request->user();
        $query = self::where('customer_id', '=', $request->selected_customer->id);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        return $query;
    }

    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order) && in_array($request->sort_order, self::SORTABLE_FIELDS)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction == "asc" ? "asc" : "desc");
        }

        return $query;
    }

    /**
     * enable automation.
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function saveData($data)
    {
        $this->data = $data;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function getData()
    {
        return isset($this->data) ? preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $this->data) : '[]';
    }

    /**
     * @param boolean $associative
     * @return array
     */
    public function getElements($associative = false)
    {
        return isset($this->data) && !empty($this->data) ? json_decode($this->data, $associative) : [];
    }

    /**
     * @return AutomationElement
     */
    public function getTrigger()
    {
        $elements = $this->getElements();

        return empty($elements) ? new AutomationElement(null) : new AutomationElement($elements[0]);
    }

    /**
     * @param null $id
     * @return AutomationElement
     */
    public function getElement($id = null)
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element->id == $id) {
                return new AutomationElement($element);
            }
        }

        return new AutomationElement(null);
    }

    /**
     * Get started time.
     */
    public function getStartedTime()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get delay options.
     */
    public function getDelayOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.minute', 1), 'value' => '1 minute'],
            ['text' => trans_choice('messages.automation.delay.minute', 30), 'value' => '30 minutes'],
            ['text' => trans_choice('messages.automation.delay.hour', 1), 'value' => '1 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 2), 'value' => '2 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 4), 'value' => '4 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 8), 'value' => '8 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 12), 'value' => '12 hours'],

            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    function getPopupOptions()
    {
        $options = [];
        $popups = $this->customer->popups;
        foreach ($popups as $popup) {
            $options[] = [
                'value' => $popup->uid,
                'text' => $popup->title
            ];
        }
        return $options;
    }

    /**
     * Get delay or before options.
     */
    public function getDelayBeforeOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.0_day', 0), 'value' => '0 day'],
            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get email links options.
     */
    public function getEmailLinkOptions()
    {
        $data = [];

        foreach ($this->emailLinks as $link) {
            $data[] = ['text' => $link->link, 'value' => $link->uid];
        }

        return $data;
    }

    /**
     * Get emails options.
     */
    public function getEmailOptions()
    {
        $data = [];

        foreach ($this->emails as $email) {
            $data[] = ['text' => $email->subject, 'value' => $email->uid];
        }

        return $data;
    }

    /**
     * Initiate a trigger with a given subscriber.
     * @param Subscriber $subscriber
     * @param array $extraData
     */
    public function initTrigger($subscriber, $extraData = [])
    {
        /** @var Segment2 $segment2 */
        $segment2 = Segment2::find($this->segment2_id);
        if ($segment2) {
            $s = $segment2->subscribers()->find($subscriber->id);
            if (!$s) return;
        }

        $this->logger()->info(sprintf('Initialized trigger %s %s %s', $subscriber->uid, $subscriber->email, $subscriber->getFullName()));
        DB::transaction(function () use ($subscriber, $extraData) {
            $trigger = new AutoTrigger();
            $this->autoTriggers()->save($trigger);
            $trigger->subscriber()->associate($subscriber); // assign to subscriber_id field
            $trigger->extra_data = $extraData;
            $trigger->updateWorkflow();
            $trigger->save();
        });
    }

    /**
     * Scan for triggers updates and new triggers.
     */
    public static function run()
    {
        DebugLog::automation2Log('run started', '', []);
        /** @var Customer[] $customers */
        $customers = Customer::all();
        foreach ($customers as $customer) {
            // Only one automation process to run at one given time

            $automations = $customer->activeAutomation2s;
            foreach ($automations as $automation) {
                // DebugLog::automation2Log('processing', $automation->name, []);
                $automationLockFile = $customer->getLockPath('automation-lock-' . $automation->uid);
                $lock = new Lockable($automationLockFile);
                $timeout = 5; // seconds

                $lock->getExclusiveLock(function ($f) use ($customer, $automation) {
                    try {
                        // DebugLog::automation2Log('got lockfile', $automation->name, []);
                        if ($customer->shopify_shop->active) {
                            $automation->logger()->info(sprintf('Checking automation "%s"', $automation->name));
                            // DebugLog::automation2Log('Checking automation', $automation->name, []);
                            $automation->checkForNewTriggers();
                            $automation->checkForExistingTriggersUpdate();
                            $automation->updateCache();
                            $automation->setLastError(null);
                        } else {
                            // DebugLog::automation2Log('skipped', $automation->name, []);
                            // DebugLog::automation2Log("skipped", $automation->uid . ' ' . $automation->name, ["Automation skipped, user  not on active subscription'"]);
                            $automation->logger()->warning(sprintf('Automation "%s" skipped, user "%s" not on active subscription', $automation->name, $customer->displayName()));
                        }
                    } catch (Exception $ex) {
                        // DebugLog::automation2Log($ex->getMessage(), $automation->name, []);
                        $automation->setLastError($ex->getMessage());
                        $automation->logger()->warning(sprintf('Error while executing automation "%s". %s. %s', $automation->name, $ex->getMessage(), $ex->getTraceAsString()));
                    }
                }, $timeout);
            }
        }
    }

    /**
     * Check for new triggers.
     */
    public function checkForNewTriggers()
    {
        $this->logger()->info(sprintf('NEW > Start checking for new trigger'));
        // DebugLog::automation2Log('NEW > Start checking for new trigger', '', []);

        switch ($this->getTriggerAction()->getOption('key')) {
            case 'welcome-new-subscriber':
                // DebugLog::automation2Log('welcome-new-subscriber', '', []);
                $this->checkForListSubscription();
                break;
            case 'specific-date':
                // DebugLog::automation2Log('specific-date', '', []);
                $this->checkForSpecificDatetime();
                break;
            case 'say-goodbye-subscriber':
                // DebugLog::automation2Log('say-goodbye-subscriber', '', []);
                $this->checkForListUnsubscription();
                break;
            case 'say-happy-birthday':
                // DebugLog::automation2Log('ay-happy-birthday', '', []);
                $this->checkForDateOfBirth();
                break;
            case 'api-3-0':
                // Just wait for API call
                break;
            case 'others':
                // others
                break;
            case 'subscriber-added-date':
                // todo: implement later
                break;
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_CHAT_ENDED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_SUBMITTED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_UPDATED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_POPUP_SUBMITTED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED:
                // triggered by shopify webhooks
                break;
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_UNATTENDED_CHAT:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_CHAT:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_POPUP_RESPONSE:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_REVIEW:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_ADDITIONAL_SHOP_ADDED:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_MISSED_CHATS:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_SALES:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_LOW_SALES:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_UNSUBSCRIBE_RATE:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_SPAM_RATE:
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_BOUNCE_RATE:
                // admin automations triggered by system
                break;
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_CHECKOUT_ABANDONED:
                // DebugLog::automation2Log('checkShopifyAbandonedCheckouts', '', []);
                $this->checkShopifyAbandonedCheckouts();
                break;
            default:
                // DebugLog::automation2Log('Unknown Automation trigger type', '', []);
                throw new Exception('Unknown Automation trigger type ' . $this->getTriggerAction()->getOption('key'));
        }
        $this->logger()->info(sprintf('NEW > Finish checking for new trigger'));
        // DebugLog::automation2Log('NEW > Finish checking for new trigger', '', []);
    }

    function checkShopifyAbandonedCheckouts()
    {
        $checkouts = ShopifyCheckout::getAbandonedCheckouts($this->customer);
        foreach ($checkouts as $checkout) {
            // DebugLog::automation2Log($checkout->id, $checkout->triggered_automations()->where('automation2_id', $this->id)->exists() ? 'true' : 'false', []);
            if ($checkout->triggered_automations()->where('automation2_id', $this->id)->exists())
                continue;

            $context = new ShopifyAutomationContext();
            $context->shopifyCustomer = $checkout->shopify_customer;
            $context->shopifyAbandonedCheckout = $checkout;
            dispatch(new RunAutomationWithContext($this, $context));
            $checkout->triggered_automations()->save($this);
            // DebugLog::automation2Log($checkout->id, 'Dispatched the checkout automation', []);
        }
    }

    public function checkForDateOfBirth()
    {
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $thisId = $this->id;

        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $subscribers = $this->subscribers()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            ->whereIn(DB::raw(sprintf('SUBSTRING(%s, 1, 10)', table('subscriber_fields.value'))), [
                $today->format('Y-m-d'),
                $today->format('Y:m:d'),
                $today->format('m/d/Y'),
                $today->format('m-d-Y'),
            ])
            ->leftJoin('auto_triggers', function ($join) use ($thisId) {
                $join->on('auto_triggers.subscriber_id', 'subscribers.id');
                $join->where('auto_triggers.automation2_id', $thisId);
            })
            ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Say happy birthday (%s) to %s', $interval, $subscriber->email));
        }
    }

    public function checkForDateOfBirthDebug()
    {
        $debug = [];
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $debug[] = sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s'));
        }

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $debug[] = 'Before: ' . $this->getTriggerAction()->getOptions()['before'];
        $debug[] = 'So, look for those whose DOB is: ' . $today->format('Y-m-d');

        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $subscribers = $this->subscribers()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            ->whereIn(DB::raw(sprintf('SUBSTRING(%s, 1, 10)', table('subscriber_fields.value'))), [
                $today->format('Y-m-d'),
                $today->format('Y:m:d'),
                $today->format('m/d/Y'),
                $today->format('m-d-Y'),
            ])
            ->leftJoin('auto_triggers', 'auto_triggers.subscriber_id', 'subscribers.id')
            ->whereNull('auto_triggers.subscriber_id')->get();

        $sql = "select `" . table('subscribers') . "`.* from `" . table('subscribers') . "` inner join `" . table('subscriber_fields') . "` on `" . table('subscribers') . "`.`id` = `" . table('subscriber_fields') . "`.`subscriber_id` left join `" . table('auto_triggers') . "` on `" . table('auto_triggers') . "`.`subscriber_id` = `" . table('subscribers') . "`.`id` and `" . table('auto_triggers') . "`.`automation2_id` = " . $this->id . " where `" . table('subscribers') . "`.`mail_list_id` = " . $this->mailList->id . " and `" . table('subscribers') . "`.`mail_list_id` is not null and `" . table('subscriber_fields') . "`.`field_id` = " . $dobFieldId . " and SUBSTRING(" . table('subscriber_fields') . ".value, 1, 10) in ('" . $today->format('Y-m-d') . "', '" . $today->format('Y:m:d') . "', '" . $today->format('m/d/Y') . "', '" . $today->format('m-d-Y') . "') and `" . table('auto_triggers') . "`.`subscriber_id` is null;";
        $debug[] = $sql;

        return $debug;
    }

    /**
     * Check for existing triggers update.
     */
    public function checkForExistingTriggersUpdate()
    {
        $this->logger()->info(sprintf('UPDATE > Start checking for trigger update'));

        /* FORCE RECHECK
        foreach ($this->autoTriggers as $trigger) {
            $trigger->check();
        }*/

        foreach ($this->pendingAutoTriggers as $trigger) {
            $trigger->check();
        }

        $this->logger()->info(sprintf('UPDATE > Finish checking for trigger update'));
    }

    /**
     * Check for list-subscription events.
     */
    public function checkForListSubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Subscription'));
        $subscribers = $this->getNewSubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > There are %s new subscriber(s) found', $total));

        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Check for specific-datetimetime events.
     */
    public function checkForSpecificDatetime()
    {
        $this->logger()->info(sprintf('NEW > Check for Specific Date/Time'));
        // this is a one-time triggered automation event
        // just abort if it is already triggered
        if ($this->autoTriggers()->exists()) {
            $this->logger()->info(sprintf('NEW > Already triggered'));

            return;
        }

        $now = Carbon::now($this->customer->timezone);
        $trigger = $this->getTriggerAction();
        $eventDate = Carbon::parse(sprintf('%s %s %s', $trigger->getOption('date'), $trigger->getOption('at'), $this->customer->timezone));
        $checked = $now->gte($eventDate);

        $total = $this->subscribers()->count();
        $i = 0;
        if ($checked) {
            $this->logger()->info(sprintf('NEW > It is %s hours due! triggering!', $now->diffInHours($eventDate)));
            foreach ($this->subscribers()->get() as $subscriber) {
                $i += 1;
                $this->initTrigger($subscriber);
                $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
            }
        }
    }

    /**
     * Check for follow-up-clicked events.
     */
    public function checkForListUnsubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Unsubscription'));
        $subscribers = $this->getUnsubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > %s new unsubscribers found', $total));
        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Get previous event's opened messages to follow up.
     */
    public function getNewSubscribersToFollow()
    {
        // Boot performance with temporary table + index
        try {
            \DB::statement('DROP TEMPORARY TABLE `_new_subscribers_to_follow`');
        } catch (Exception $ex) {
            // just ignore, in case 2 queries in the same MySQL connection session
        }

        \DB::statement(
            sprintf('
            CREATE TEMPORARY TABLE `_new_subscribers_to_follow` AS
            SELECT COALESCE(subscriber_id, 0) AS subscriber_id
            FROM %s WHERE automation2_id = %s;

            CREATE INDEX _new_subscribers_to_follow_index ON _new_subscribers_to_follow(subscriber_id);', table('auto_triggers'), $this->id)
        );

        return $this->subscribers()->whereRaw(table('subscribers.id') . ' NOT IN (SELECT subscriber_id FROM _new_subscribers_to_follow)')
            ->where('subscribers.created_at', '>=', $this->created_at)
            ->where('mail_list_id', $this->mailList->id)
            ->whereRaw(sprintf('COALESCE(' . table('subscribers.subscription_type') . ", '') <> %s", db_quote(Subscriber::SUBSCRIPTION_TYPE_IMPORTED)))->get();
    }

    /**
     * Get previous event's opened messages to follow up.
     */
    public function getNewSubscribersToFollowDebug()
    {
        $tmpSql = sprintf('
            CREATE TEMPORARY TABLE `_new_subscribers_to_follow` AS
            SELECT COALESCE(subscriber_id, 0) AS subscriber_id
            FROM %s WHERE automation2_id = %s;

            CREATE INDEX _new_subscribers_to_follow_index ON _new_subscribers_to_follow(subscriber_id);', table('auto_triggers'), $this->id);

        $newSubscribersSql = $this->subscribers()->whereRaw(table('subscribers.id') . ' NOT IN (SELECT subscriber_id FROM _new_subscribers_to_follow)')
            ->where('subscribers.created_at', '>=', $this->created_at)
            ->where('mail_list_id', $this->mailList->id)
            ->whereRaw(sprintf('COALESCE(' . table('subscribers.subscription_type') . ", '') <> %s", db_quote(Subscriber::SUBSCRIPTION_TYPE_IMPORTED)))
            ->toSql();

        return prettifystr($tmpSql . "; " . $newSubscribersSql);
    }

    /**
     * Get previous event's unsubscribed messages to follow up.
     */
    public function getUnsubscribersToFollow()
    {
        return $this->subscribers()
            ->select('subscribers.*')
            ->join('unsubscribe_logs', 'subscribers.id', '=', 'unsubscribe_logs.subscriber_id')
            ->leftJoin('auto_triggers', 'subscribers.id', '=', 'auto_triggers.subscriber_id')
            ->whereNull('auto_triggers.id')
            ->where('unsubscribe_logs.created_at', '>=', $this->created_at)
            ->get();
    }

    public function logger(): Logger
    {
        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");
        if (getenv('LOGFILE') != false) {
            $stream = new RotatingFileHandler(getenv('LOGFILE'), 0, Logger::DEBUG);
        } else {
            $logfile = storage_path('logs/' . php_sapi_name() . '/automation-' . $this->uid . '.log');
            $stream = new RotatingFileHandler($logfile, 0, Logger::DEBUG);
        }

        $stream->setFormatter($formatter);

        $pid = getmypid();
        $logger = new Logger($pid);
        $logger->pushHandler($stream);

        return $logger;
    }

    public function timelinesBy($subscriber)
    {
        $trigger = $this->autoTriggers()->where('subscriber_id', $subscriber->id)->first();

        return (is_null($trigger)) ? Timeline::whereRaw('1=2') : $trigger->timelines(); // trick - return an empty Timeline[] array
    }

    public function getInsight()
    {
        if (!$this->data) {
            return [];
        }

        $actions = json_decode($this->data, true);
        $insights = [];
        foreach ($actions as $action) {
            $insights[$action['id']] = $this->getActionStats($action);
        }

        return $insights;
    }

    public function getActionStats($attributes)
    {
        $total = $this->autoTriggers()->count();

        // The following implementation is prettier but 'countBy' is supported in Laravel 5.8 only
        // $count = $this->autoTriggers()->countBy(function($trigger) {
        //    return $trigger->isActionExecuted($action['id']);
        // });

        // IMPORTANT: this action does not associate with a particular trigger
        $action = $this->getAction($attributes);

        // @DEPRECATED
        // $count = 0;
        // foreach ($this->autoTriggers as $trigger) {
        //     $count += ($trigger->isActionExecuted($action->getId())) ? 1 : 0;
        // }

        // Count the number subscribers whose action has been executed
        $count = $this->subscribersByExecutedAction($action->getId())->count();

        if ($action->getType() == 'ElementTrigger') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.triggered', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementOperation') {
            $count = 0;
            foreach ($this->autoTriggers as $trigger) {
                $count += ($trigger->isLatest($action->getId())) ? 1 : 0;
            }

            $insight = [
                'count' => $count,
                'subtitle' => '#Operation TBD',
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementWait') {
            // Count the numbe contacts with this action as last executed one
            $queue = $this->subscribersByLatestAction($action->getId())->count();

            // since the previous $count also covers $queue ones, so get the already passed one by subtracting
            $passed = $count - $queue;

            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.in-queue2', ['queue' => $queue, 'passed' => $passed]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementAction') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.sent', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementCondition') {
            $yes = $this->subscribersByExecutedAction($action->getChildYesId())->count();
            $no = $this->subscribersByExecutedAction($action->getChildNoId())->count();

            $insight = [
                'count' => $yes + $no,
                'subtitle' => __('messages.automation.stats.condition', ['yes' => $yes, 'no' => $no]),
                'percentage' => ($total != 0) ? (($yes + $no) / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        }

        return $insight;
    }

    public function getSummaryStats(): array
    {
        $total = $this->mailList->subscribersCount();
        $involved = $this->autoTriggers()->count();
        $total_emails = $this->countEmails();
        $total_sent_emails = $this->email_actions()->count();

        $den = $total_emails * $involved;
        $completePercentage = ($den == 0) ? 0 : $total_sent_emails / $den;

        return [
            'total' => $total,
            'involed' => $involved,
            'complete' => $completePercentage,
        ];
    }

    // for debugging only
    public function getTriggerAction(): ?Trigger
    {
        $trigger = null;
        $this->getActions(function ($e) use (&$trigger) {
            if ($e->getType() == 'ElementTrigger') {
                $trigger = $e;
            }
        });

        return $trigger;
    }

    // by Louis
    public function getActions($callback)
    {
        $actions = $this->getElements(true);

        foreach ($actions as $action) {
            $instance = $this->getAction($action);
            $callback($instance);
        }
    }

    // by Louis
    public function getLeafActions()
    {
        $leaves = [];
        $actions = $this->getElements(true);

        $this->getActions(function ($e) use (&$leaves) {
            if ($e->getNextActionId() == null) {
                $leaves[] = $e->getId();
            }
        });

        return $leaves;
    }

    // IMPORTANT: object returned by this function is not associated with a particular AutoTrigger
    public function getAction($attributes): Action
    {
        switch ($attributes['type']) {
            case 'ElementTrigger':
                $instance = new Trigger($attributes);
                break;
            case 'ElementAction':
                $instance = new Send($attributes);
                break;
            case 'ElementCondition':
                $instance = new Evaluate($attributes);
                break;
            case 'ElementWait':
                $instance = new Wait($attributes);
                break;
            case 'ElementOperation':
                $instance = new Operate($attributes);
                break;
            default:
                throw new Exception('Unknown Action type ' . $attributes['type']);
        }

        return $instance;
    }

    // get all subscribers
    // if $actionId is provided, get only subscribers who have been triggered and have gone through the action
    public function subscribers($actionId = null)
    {
        $query = $this->mailList->subscribers()->select('subscribers.*');

        if (!is_null($actionId)) {
            // @deprecated, use the subscribersByLatestAction() method instead
            $query->join('auto_triggers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                ->where('auto_triggers.executed_index', 'LIKE', '%' . $actionId . '%')
                ->where('auto_triggers.automation2_id', $this->id);
        }

        return $query;
    }

    public function subscribersByLatestAction($actionId): HasMany
    {
        return $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
            ->where('auto_triggers.executed_index', 'LIKE', '%' . $actionId)->select('subscribers.*');
    }

    public function subscribersByExecutedAction($actionId): HasMany
    {
        return $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
            ->where('auto_triggers.executed_index', 'LIKE', '%' . $actionId . '%')->select('subscribers.*');
    }

    public function getIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.intro.' . $triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function getBriefIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.brief-intro.' . $triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function countEmails(): int
    {
        $count = 0;
        $this->getActions(function ($e) use (&$count) {
            if ($e->getType() == 'ElementAction') {
                $count += 1;
            }
        });

        return $count;
    }

    /**
     * Get recent automations for switch.
     */
    public function getSwitchAutomations($customer)
    {
        return $customer->automation2s()->where('id', '<>', $this->id)->orderBy('updated_at', 'desc')->limit(50);
    }

    /**
     * Get list fields options.
     */
    public function getListFieldOptions()
    {
        $data = [];

        foreach ($this->mailList->getFields()->get() as $field) {
            $data[] = ['text' => $field->label, 'value' => $field->uid];
        }

        return $data;
    }

    /**
     * Produce sample data.
     */
    public function produceSampleData()
    {
        // Reset all
        $this->resetListRelatedData();

        $count = $this->mailList->readCache('SubscriberCount');

        $min = (int)($count * 0.2);
        $max = (int)($count * 0.7);

        // Generate triggers
        $subscribers = $this->subscribers()->inRandomOrder()->limit(rand($min, $max))->get();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }

        // Run through trigger check
        $this->checkForExistingTriggersUpdate();
    }

    /**
     * Clean up after list change.
     */
    public function resetListRelatedData()
    {
        // Delete autoTriggers will also delete
        // + tracking_logs
        // + open logs
        // + click logs
        // + timelines
        $this->autoTriggers()->delete();
        $this->updateCache();
    }

    /**
     * Change mail list.
     */
    public function updateMailList($new_list)
    {
        if ($this->mail_list_id != $new_list->id) {
            $this->mail_list_id = $new_list->id;
            $this->save();

            // reset automation list
            $this->resetListRelatedData();
        }
    }

    public function getTriggerTypes()
    {
        $types = [
            'welcome-new-subscriber',
            //'say-happy-birthday',
            //'subscriber-added-date',
            'specific-date',
            'say-goodbye-subscriber',
            'api-3-0',
        ];

        $mailList = $this->mailList;
        if ($mailList->shopify_shop_id) {
            $types = array_merge(ShopifyAutomationContext::AUTOMATION_TRIGGERS, $types);
            if ($this->customer->user->isAdmin())
                $types = array_merge(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGERS, $types);
        }

        return $types;
    }

    /**
     * Fill from request.
     */
    public function fillRequest($request)
    {
        // fill attributes
        $this->fill($request->all());

        // fill segments
        $segments = [];
        $this->segment_id = null;
        if (!empty($request->segment_uid)) {
            foreach ($request->segment_uid as $segmentUid) {
                $segments[] = Segment::findByUid($segmentUid)->id;
            }

            if (!empty($segments)) {
                $this->segment_id = implode(',', $segments);
            }
        }
    }

    /**
     * Get segments.
     */
    public function getSegments()
    {
        if (!$this->segment_id) {
            return collect([]);
        }

        $segments = Segment::whereIn('id', explode(',', $this->segment_id))->get();

        return $segments;
    }

    /**
     * Get segments uids.
     */
    public function getSegmentUids()
    {
        return $this->getSegments()->map->uid->toArray();
    }

    // for debugging only
    public function updateActionOptions($actionId, $data = [])
    {
        $json = json_decode($this->data, true);

        for ($i = 0; $i < sizeof($json); $i += 1) {
            $action = $json[$i];
            if ($action['id'] != $actionId) {
                continue;
            }

            $action['options'] = array_merge($action['options'], $data);

            $json[$i] = $action;
            $this->data = json_encode($json);
            $this->save();
        }
    }

    /**
     * Get segments uids.
     */
    public function execute()
    {
        $this->logger()->info(sprintf('Executed automation2'));
        $this->logger()->info(sprintf('Active subscribers: %d', $this->mailList->activeSubscribers()->count()));
        $subscribers = $this->getNotTriggeredSubscribers();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }
    }

    public function getNotTriggeredSubscribers()
    {
        return $this->mailList->activeSubscribers()
            ->leftJoin('auto_triggers', function ($join) {
                $join->on('subscribers.id', '=', 'auto_triggers.subscriber_id')->where('auto_triggers.automation2_id', '=', $this->id);
            })
            ->whereNull('auto_triggers.id')->select('subscribers.*')->get();
    }

    public function allowApiCall()
    {
        // Usually invoked by API call
        $type = $this->getTriggerAction()->getOption('key');

        return $type == 'api-3-0';
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'SummaryStats' => function (&$auto) {
                return $auto->getSummaryStats();
            }
        ];

        // retrieve cached data
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            $cache = [];
        }

        if (is_null($key)) {
            // update all cache
            foreach ($index as $key => $callback) {
                $cache[$key] = $callback($this);
                if ($key == 'SubscriberCount') {
                    // SubscriberCount cache must always be updated as its value will be used for the others
                    $this->cache = json_encode($cache);
                    $this->save();
                }
            }
        } else {
            // update specific key
            $callback = $index[$key];
            $cache[$key] = $callback($this);
        }

        // write back to the DB
        $this->cache = json_encode($cache);
        $this->save();
    }

    /**
     * Retrieve Campaign cached data.
     *
     * @return mixed
     */
    public function readCache($key, $default = null)
    {
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            return $default;
        }
        if (array_key_exists($key, $cache)) {
            if (is_null($cache[$key])) {
                return $default;
            } else {
                return $cache[$key];
            }
        } else {
            return $default;
        }
    }

    public function updateCacheInBackground()
    {
        $existed = SystemJobModel::getNewJobs()
            ->where('name', UpdateAutomation::class)
            ->where('data', $this->id)
            ->exists();

        if (!$existed) {
            dispatch(new UpdateAutomation($this));
        }
    }

    public function setLastError($message)
    {
        $this->last_error = $message;
        $this->save();
    }

    public function makeCopy($name, Customer $newCustomer): ?self
    {
        $mail_list = $newCustomer->getDefaultMailingList();
        if (!$mail_list) return null;

        $new = new self();
        $new->name = $name ?: $this->name . " copy";
        $new->customer_id = $newCustomer->id;
        $new->mail_list_id = $mail_list->id;
        $new->time_zone = $this->time_zone;
        $new->data = $this->data;
        $new->segment_id = $this->segment_id;
        $new->segment2_id = $this->segment2_id;
        $new->trigger_key = $this->trigger_key;
        $new->cache = "";
        $new->last_error = "";
        $new->status = Automation2::STATUS_INACTIVE;
        $new->save();

        $old_email_uids = [];
        $new_email_uids = [];
        foreach ($this->emails as $email) {
            $old_email_uids[] = $email->uid;
            $new_email = $email->makeCopy($new);
            $new_email_uids[] = $new_email->uid;
        }

        $new->data = str_replace($old_email_uids, $new_email_uids, $new->data);
        $new->save();

        if ($newCustomer->id != $this->customer_id) {
            $newCustomer->automations_imported_at = Carbon::now();
            $newCustomer->save();
        }

        return $new;
    }

    /** @return self[] */
    static function getPublicAutomations()
    {
        return self::where(self::COLUMN_default_for_new_customers, 1)->get();
    }

    static function getNewPublicAutomationsCount(Customer $customer): int
    {

        $query = self::where(self::COLUMN_default_for_new_customers, 1);
        if ($customer->automations_imported_at != null)
            $query->where(self::COLUMN_updated_at, '>', $customer->automations_imported_at);
        return $query->count();
    }
}
