<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string uid
 * @property string myshopify_domain
 * @property string primary_domain
 */
class TrialRecord extends Model
{
    const COLUMN_uid = 'uid';
    const COLUMN_myshopify_domain = 'myshopify_domain';
    const COLUMN_primary_domain = 'primary_domain';

    static function storeRecord(ShopifyShop $shop)
    {
        if (TrialRecord::recordExists($shop->myshopify_domain)
            || TrialRecord::recordExists($shop->primary_domain))
            return;

        $model = new self();
        $model->myshopify_domain = $shop->myshopify_domain;
        $model->primary_domain = $shop->primary_domain;
        $model->save();
    }

    static function recordExists($name): bool
    {
        return self::query()
            ->where(self::COLUMN_primary_domain, $name)
            ->orWhere(self::COLUMN_myshopify_domain, $name)
            ->exists();
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
