<?php

namespace Acelle\Model;

use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PopupResponse
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer popup_id
 * @property integer customer_id
 * @property string email
 * @property string first_name
 * @property string last_name
 * @property array extra_data
 * @property integer subscriber_id
 *
 * @property Customer customer
 * @property Popup popup
 * @property Subscriber subscriber
 */
class PopupResponse extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_uid = 'uid';
    const COLUMN_popup_id = 'popup_id';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_email = 'email';
    const COLUMN_first_name = 'first_name';
    const COLUMN_last_name = 'last_name';
    const COLUMN_extra_data = 'extra_data';
    const COLUMN_subscriber_id = 'subscriber_id';

    protected $casts = [
        self::COLUMN_extra_data => 'array'
    ];
    protected $hidden = [
        'popup'
    ];

    protected $appends = ['popup_title'];

    function getPopupTitleAttribute(): string
    {
        return $this->popup ? $this->popup->title : "Deleted popup";
    }

    function getOrCreateSubscriber()
    {
        if ($this->subscriber)
            return $this->subscriber;

        $subscriber = Subscriber::createSubscriber($this->popup->customer->shopify_shop->mail_list,
            $this->email,
            $this->first_name,
            $this->last_name
        );
        $this->subscriber_id = $subscriber->id;
        $this->save();
        return Subscriber::find($this->subscriber_id);
    }

    function triggerAutomations()
    {
        $customer = $this->popup->customer;
        foreach ($customer->automation2s as $automation2) {
            if ($automation2->trigger_key == ShopifyAutomationContext::AUTOMATION_TRIGGER_POPUP_SUBMITTED) {
                $trigger = $automation2->getTrigger();
                if ($trigger->getOption('popup_uid') == $this->popup->uid) {
                    $context = new ShopifyAutomationContext();
                    $context->popupResponse = $this;
                    $context->subscriber = $this->getOrCreateSubscriber();
                    dispatch(new RunAutomationWithContext($automation2, $context));
                }
            }
        }
    }

    function triggerFirstPopupResponseAutomation()
    {
        $count = $this->popup->responses()->count();
        if ($count > 1) return;

        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_POPUP_RESPONSE);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->customer->user->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->popupResponse = $this;
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    static function getTotalCount($customer, $start_date, $end_date): int
    {
        $query = self::query()->where(self::COLUMN_customer_id, $customer->id);
        if ($end_date)
            $query->where(self::COLUMN_created_at, '<=', $end_date);
        if ($start_date)
            $query->where(self::COLUMN_created_at, '>=', $start_date);

        return $query->count();
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, self::COLUMN_customer_id);
    }

    function popup(): BelongsTo
    {
        return $this->belongsTo(Popup::class, self::COLUMN_popup_id);
    }

    function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, self::COLUMN_subscriber_id);
    }

    /**
     * @return self|null
     */
    static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    static function store_response_rules()
    {
        return [
            'uid' => 'required|string|exists:popups,uid',
            PopupResponse::COLUMN_email => 'required|email|max:255',
            PopupResponse::COLUMN_first_name => 'string|max:255',
            PopupResponse::COLUMN_last_name => 'string|max:255'
        ];
    }

    static function store_response(Popup $popup, $data)
    {
        if (!$popup) return false;

        $model = new self();

        $model->email = $data[self::COLUMN_email] ?? "";
        $model->first_name = $data[self::COLUMN_first_name] ?? "";
        $model->last_name = $data[self::COLUMN_last_name] ?? "";
        $model->extra_data = $data[self::COLUMN_extra_data] ?? [];
        $model->popup_id = $popup->id;
        $model->customer_id = $popup->customer_id;
        $model->save();

        return $model;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $item) {
            $item->getOrCreateSubscriber();
            $item->triggerAutomations();
            $item->triggerFirstPopupResponseAutomation();
        });
    }
}
