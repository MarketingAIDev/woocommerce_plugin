<?php

namespace Acelle\Model;

use Acelle\ShopifyModel\Blog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property string created_at
 * @property string updated_at
 * @property string uid
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property string shopify_id
 * @property string data
 * @property string shopify_title
 */
class ShopifyBlog extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_data = 'data';
    const COLUMN_shopify_title = 'shopify_title';

    protected $hidden = ['data'];

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
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
        $model->shopify_title = "";

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }

    static function storeBlog(ShopifyShop $shop, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->shopify_title = $model->getShopifyModel()->title;

        $model->save();
        return $model;
    }

    /**
     * @return Blog
     */
    function getShopifyModel()
    {
        return json_decode($this->data, false);
    }

    /**
     * Find item by uid.
     *
     * @param string $uid
     * @return self
     */
    static function findByUid($uid)
    {
        return self::where(self::COLUMN_uid, $uid)->first();
    }

    /**
     * Find item by shopify id.
     *
     * @param string $id
     * @return self
     */
    static function findByShopifyId($id)
    {
        return self::where(self::COLUMN_shopify_id, $id)->first();
    }

    /**
     * @param $ids
     * @return self[]
     */
    static function findByShopifyIds($ids)
    {
        return self::whereIn(self::COLUMN_shopify_id, $ids)->get();
    }

    static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    public static function scopeSearch($query, $search)
    {
        $query->where(self::COLUMN_shopify_title, 'like', "%$search%");
        return $query;
    }
}
