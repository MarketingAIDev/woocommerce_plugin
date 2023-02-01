<?php

namespace Acelle\Model;

use Acelle\Helpers\ShopifyHelper;
use Acelle\ShopifyModel\RecurringApplicationCharge;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property string uid
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property integer customer_id
 * @property Customer customer
 * @property integer plan_id
 * @property Plan plan
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property string shopify_id
 * @property string status
 * @property string data
 * @property double price
 * @property double usage_rate
 * @property Carbon trial_ends_at
 */
class ShopifyRecurringApplicationCharge extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_plan_id = 'plan_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_status = 'status';
    const COLUMN_data = 'data';
    const COLUMN_price = 'price';
    const COLUMN_usage_rate = 'usage_rate';
    const COLUMN_trial_ends_at = 'trial_ends_at';

    const STATUS_unknown = 'unknown';
    const STATUS_cancelled = 'cancelled';
    const STATUS_frozen = 'frozen';
    const STATUS_expired = 'expired';
    const STATUS_declined = 'declined';
    const STATUS_active = 'active';
    const STATUS_pending = 'pending';

    protected $dates = [
        self::COLUMN_trial_ends_at
    ];

    protected $hidden = ['data'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    static function findActiveCharges(): Builder
    {
        return self::query()->where(self::COLUMN_status, self::STATUS_active);
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

        return $model;
    }

    static function storeRecurringApplicationCharge(ShopifyShop $shop, Plan $plan, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->status = $data['status'] ?? "";
        $model->plan_id = $plan->id;
        $model->price = $plan->price;
        $model->usage_rate = $plan->usage_rate;
        $model->trial_ends_at = Carbon::parse($model->getShopifyModel()->trial_ends_on ?? null)->setTimezone('UTC');
        $model->save();
        return $model;
    }

    function isStillActive(): bool
    {
        $helper = new ShopifyHelper($this->shop);
        $helper->getRecurringApplicationChargeStatus($this);

        return $this->status == self::STATUS_active;
    }

    function isInTrial(): bool
    {
        return $this->trial_ends_at->isFuture();
    }

    /**
     * @return RecurringApplicationCharge
     */
    function getShopifyModel()
    {
        return json_decode($this->data, false);
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUid($uid): ?self
    {
        return self::where('uid', '=', $uid)->first();
    }

    public static function findByShopifyId($id): ?self
    {
        return self::where('shopify_id', '=', $id)->first();
    }

    public static function findByShopifyIdOrFail($id): self
    {
        $model = self::findByShopifyId($id);
        if (!$model) {
            throw new \Exception('The charge was not found!');
        }
        return $model;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            do {
                $uid = uniqid();
            } while (self::where('uid', '=', $uid)->count() > 0);
            $item->uid = $uid;
        });
    }
}
