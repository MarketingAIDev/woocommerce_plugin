<?php

namespace Acelle\Model;

use Acelle\ShopifyModel\Fulfillment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property string uid
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property integer shopify_order_id
 * @property ShopifyOrder shopify_order
 * @property integer shopify_customer_id
 * @property ShopifyCustomer shopify_customer
 * @property string shopify_id
 * @property string data
 * @property string shopify_shipment_status
 * @property string|Carbon|null shopify_created_at
 * @property string|Carbon|null shopify_updated_at
 */
class ShopifyFulfillment extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_customer_id = 'shopify_customer_id';
    const COLUMN_shopify_order_id = 'shopify_order_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_data = 'data';
    const COLUMN_shopify_shipment_status = 'shopify_shipment_status';
    const COLUMN_shopify_created_at = 'shopify_created_at';
    const COLUMN_shopify_updated_at = 'shopify_updated_at';

    protected $hidden = ['data'];

    protected $dates = [
        self::COLUMN_shopify_created_at,
        self::COLUMN_shopify_updated_at,
    ];

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    public function shopify_order(): BelongsTo
    {
        return $this->belongsTo(ShopifyOrder::class);
    }

    public function shopify_customer(): BelongsTo
    {
        return $this->belongsTo(ShopifyCustomer::class);
    }

    static function findByShopAndId(ShopifyShop $shop, string $shopify_id): ?self
    {
        return self::where(self::COLUMN_shop_id, $shop->id)
            ->where(self::COLUMN_shopify_id, $shopify_id)
            ->first();
    }

    function setFulfillmentStatus(string $status)
    {
        $this->shopify_shipment_status = $status;
        $this->shopify_updated_at = Carbon::now();
        $this->save();
    }

    static function findByShopAndIdOrCreate(ShopifyShop $shop, string $shopify_id): ?self
    {
        $model = new self();
        $model->shopify_id = $shopify_id;
        $model->shop_id = $shop->id;
        $model->customer_id = $shop->customer_id;
        $model->shopify_order_id = 0;
        $model->shopify_customer_id = 0;
        $model->data = "[]";
        $model->shopify_shipment_status = "";
        $model->shopify_created_at = Carbon::now();
        $model->shopify_updated_at = Carbon::now();

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }


    static function storeFulfillment(ShopifyShop $shop, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;


        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->shopify_shipment_status = $model->getShopifyModel()->shipment_status ?? "";

        $order_shopify_id = $model->getShopifyModel()->order_id ?? "";
        $order = ShopifyOrder::findByShopifyId($order_shopify_id);
        if(!$order) return false;

        $model->shopify_order_id = $order->id;
        $model->shopify_customer_id = $order->shopify_customer_id;
        $model->shopify_created_at = Carbon::parse($model->getShopifyModel()->created_at)->setTimezone('UTC');
        $model->shopify_updated_at = Carbon::parse($model->getShopifyModel()->updated_at)->setTimezone('UTC');

        $model->save();
        return $model;
    }

    /**
     * @return Fulfillment
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
    public static function findByUid($uid)
    {
        return self::query()->where('uid', '=', $uid)->first();
    }

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::query()->where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }
}
