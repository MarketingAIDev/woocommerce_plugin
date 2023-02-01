<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string uid
 * @property string popup_id
 * @property string customer_id
 * @property string ip_address
 *
 * @property Popup popup
 */
class PopupImpression extends Model
{
    const COLUMN_uid = 'uid';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_popup_id = 'popup_id';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_ip_address = 'ip_address';

    static function storeImpression(Popup $popup)
    {
        $model = new self();
        $model->popup_id = $popup->id;
        $model->customer_id = $popup->customer_id;
        $model->ip_address = request()->ip();
        $model->save();
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

    function popup(): BelongsTo
    {
        return $this->belongsTo(Popup::class, self::COLUMN_popup_id);
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
