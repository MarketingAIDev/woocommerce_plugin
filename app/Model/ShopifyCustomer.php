<?php

namespace Acelle\Model;

use Acelle\ShopifyModel\Customer as ShopifyCustomerModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer id
 * @property string uid
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property integer subscriber_id
 * @property Subscriber subscriber
 * @property integer shopify_id
 * @property string data
 * @property string country_code
 * @property boolean accepts_marketing
 * @property string|Carbon|null accepts_marketing_updated_at
 * @property string currency
 * @property string|Carbon|null shopify_created_at
 * @property string email
 * @property string first_name
 * @property string last_name
 * @property integer orders_count
 * @property string state
 * @property double total_spent
 * @property double note
 * @property string phone_number
 * @property boolean verified_email
 *
 * @property ShopifyCheckout[] shopify_checkouts
 * @property ShopifyOrder[] shopify_orders
 * @property ShopifyFulfillment[] shopify_fulfillments
 */
class ShopifyCustomer extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_subscriber_id = 'subscriber_id';
    const COLUMN_data = 'data';

    // For segmentation
    const COLUMN_country_code = 'country_code';
    const COLUMN_accepts_marketing = 'accepts_marketing';
    const COLUMN_accepts_marketing_updated_at = 'accepts_marketing_updated_at';
    const COLUMN_currency = 'currency';
    const COLUMN_shopify_created_at = 'shopify_created_at';
    const COLUMN_email = 'email';
    const COLUMN_first_name = 'first_name';
    const COLUMN_last_name = 'last_name';
    const COLUMN_orders_count = 'orders_count';
    const COLUMN_state = 'state';
    const COLUMN_total_spent = 'total_spent';
    const COLUMN_note = 'note';
    const COLUMN_phone_number = 'phone_number';
    const COLUMN_verified_email = 'verified_email';

    protected $hidden = ['data'];

    protected $casts = [
        self::COLUMN_accepts_marketing => 'boolean',
        self::COLUMN_verified_email => 'boolean',
    ];

    protected $dates = [
        self::COLUMN_accepts_marketing_updated_at,
        self::COLUMN_shopify_created_at,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, self::COLUMN_subscriber_id);
    }

    function shopify_checkouts(): HasMany
    {
        return $this->hasMany(ShopifyCheckout::class, ShopifyCheckout::COLUMN_shopify_customer_id);
    }

    function shopify_orders(): HasMany
    {
        return $this->hasMany(ShopifyOrder::class, ShopifyOrder::COLUMN_shopify_customer_id);
    }

    function shopify_fulfillments(): HasMany
    {
        return $this->hasMany(ShopifyFulfillment::class, ShopifyFulfillment::COLUMN_shopify_customer_id);
    }

    function hasUsedDiscountCoupon($coupon)
    {
        foreach ($this->shopify_orders as $order) {
            foreach ($order->getShopifyModel()->discount_codes as $code) {
                if ($code->code == $coupon)
                    return true;
            }
        }
        return false;
    }

    function setSegmentationProperties()
    {
        $m = $this->getShopifyData();
        if ($m) {
            if (!empty($m['default_address']) && !empty($m['default_address']['country_code']))
                $this->country_code = $m['default_address']['country_code'];

            $this->accepts_marketing = $m['accepts_marketing'] ?? false;
            $this->accepts_marketing = Carbon::parse($m['accepts_marketing_updated_at'] ?? "now")->setTimezone('UTC');
            $this->currency = $m['currency'] ?? "";
            $this->shopify_created_at = Carbon::parse($m['created_at'] ?? "now")->setTimezone('UTC');
            $this->email = $m['email'] ?? "";
            $this->first_name = $m['first_name'] ?? "";
            $this->last_name = $m['last_name'] ?? "";
            $this->orders_count = $m['orders_count'] ?? 0;
            $this->state = $m['state'] ?? "";
            $this->total_spent = $m['total_spent'] ?? 0;
            $this->note = $m['note'] ?? "";
            $this->phone_number = $m['phone'] ?? "";
            $this->verified_email = $m['verified_email'] ?? false;
        }
        $this->save();
    }

    function getOrCreateSubscriber()
    {
        if ($this->subscriber)
            return $this->subscriber;

        DebugLog::automation2Log('get_or_create_subscriber', $this->shopify_id, [print_r($this->getShopifyModel(), true)]);

        $timestamp = null;
        if ($this->getShopifyModel()->created_at) {
            $timestamp = Carbon::parse($this->getShopifyModel()->created_at)->setTimezone('UTC');
        }

        $subscriber = Subscriber::createSubscriber($this->shop->mail_list,
            $this->getShopifyModel()->email,
            $this->getShopifyModel()->first_name,
            $this->getShopifyModel()->last_name,
            $timestamp
        );
        $this->subscriber_id = $subscriber->id;
        $this->save();
        return Subscriber::find($this->subscriber_id);
    }


    static function findByShopAndId(ShopifyShop $shop, string $shopify_id): ?self
    {
        return self::where(self::COLUMN_shop_id, $shop->id)
            ->where(self::COLUMN_shopify_id, $shopify_id)
            ->first();
    }

    static function findByShopAndIdOrCreate(ShopifyShop $shop, string $shopify_id): ?self
    {
        $model = new self();
        $model->shopify_id = $shopify_id;
        $model->shop_id = $shop->id;
        $model->customer_id = $shop->customer_id;
        $model->data = "[]";

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }

    static function storeCustomer(ShopifyShop $shop, array $data)
    {
        if (empty($shop) || empty($shop->mail_list)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $email = $data['email'] ?? null;
        if (empty($email)) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->save();
        $model->setSegmentationProperties();
        $model->getOrCreateSubscriber();

        return $model;
    }

    /**
     * @return ShopifyCustomerModel
     */
    function getShopifyModel()
    {
        return json_decode($this->data, false);
    }

    /**
     * @return ShopifyCustomerModel
     */
    function getShopifyData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function (self $item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });

        static::updated(function (self $item) {
            // todo: update the subscriber
        });

        static::deleting(function (self $item) {
            $item->subscriber()->delete();
        });
    }
}
