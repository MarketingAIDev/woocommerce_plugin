<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class ShopifyUsageCharge extends Model
{
    const COLUMN_customer_id = 'customer_id';

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            do {
                $uid = uniqid();
            } while (self::where('uid', '=', $uid)->count() > 0);
            $item->uid = $uid;
        });
    }
}
