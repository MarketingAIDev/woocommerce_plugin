<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string uid
 * @property string created_at
 * @property string updated_at
 * @property integer customer_id
 * @property integer plan_id
 * @property integer msg_id
 * @property double usage_charge
 * @property boolean charged
 */
class ShopifyUsageChargeEntry extends Model
{
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_plan_id = 'plan_id';
    const COLUMN_msg_id = 'msg_id';
    const COLUMN_usage_charge = 'usage_charge';
    const COLUMN_charged = 'charged';

    protected $casts = [
        self::COLUMN_charged => "boolean"
    ];

    static function getTotalExpense(Customer $customer, Carbon $start_date, Carbon $end_date)
    {
        $charge = $customer->getActiveShopifyRecurringApplicationCharge();
        $fixed_cost = 0;
        if ($charge) {
            $charge_start = $charge->created_at;
            $actual_start = $start_date->max($charge_start);
            $actual_end = $end_date->max($charge_start);
            $days = $actual_end->diffInDays($actual_start);
            $fixed_cost = $charge->price * $days / 30;
        }

        $free_emails = $customer->getMaxFreeEmails();
        $query = self::query()
            ->where(self::COLUMN_customer_id, $customer->id)
            ->where('created_at', '<=', $end_date)
            ->where('created_at', '>=', $start_date)
            ->skip($free_emails)
            ->take(PHP_INT_MAX);

        $usage_cost = $query->sum(self::COLUMN_usage_charge);
        return $fixed_cost + $usage_cost;
    }

    static function findUsageEntries(Customer $customer, $month, $year)
    {
        $start = Carbon::createFromDate($year, $month, 1);
        $end = $start->clone()->addMonth();

        return self::query()
            ->where(self::COLUMN_customer_id, $customer->id)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->get();
    }

    static function storeEntry(ShopifyRecurringApplicationCharge $charge, $msgId)
    {
        $model = new self();
        $model->customer_id = $charge->customer_id;
        $model->plan_id = $charge->plan_id;
        $model->msg_id = $msgId;
        $model->usage_charge = $charge->plan->usage_rate;
        $model->charged = false;
        $model->save();
    }

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
