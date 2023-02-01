<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer shopify_shop_id
 * @property ShopifyShop shopify_shop
 * @property integer customers_synced
 * @property integer customers_total
 * @property integer products_synced
 * @property integer products_total
 * @property integer orders_synced
 * @property integer orders_total
 * @property integer carts_synced
 * @property integer carts_total
 */
class SyncStatus extends Model
{
    const COLUMN_shopify_shop_id = 'shopify_shop_id';
    const COLUMN_customers_synced = 'customers_synced';
    const COLUMN_customers_total = 'customers_total';
    const COLUMN_products_synced = 'products_synced';
    const COLUMN_products_total = 'products_total';
    const COLUMN_orders_synced = 'orders_synced';
    const COLUMN_orders_total = 'orders_total';
    const COLUMN_carts_synced = 'carts_synced';
    const COLUMN_carts_total = 'carts_total';

    function shopify_shop()
    {
        return $this->belongsTo(ShopifyShop::class, self::COLUMN_shopify_shop_id);
    }

    static function getSyncStatus(ShopifyShop $shop): self
    {
        $model = new self();
        $model->customers_synced = 0;
        $model->customers_total = 0;
        $model->products_synced = 0;
        $model->products_total = 0;
        $model->orders_synced = 0;
        $model->orders_total = 0;
        $model->carts_synced = 0;
        $model->carts_total = 0;

        if($shop->sync_status)
            return $shop->sync_status;

        $shop->sync_status()->save($model);
        return $model;
    }
}
