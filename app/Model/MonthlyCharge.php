<?php

namespace Acelle\Model;

use Acelle\Helpers\ShopifyHelper;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * @property integer id
 * @property string uid
 * @property integer recurring_charge_id
 * @property integer customer_id
 * @property integer shop_id
 * @property integer plan_id
 * @property integer month
 * @property integer year
 * @property double fixed_price
 * @property double usage_charges
 * @property boolean usage_charges_billed
 * @property boolean affiliate_notified
 * @property integer paid_email_count
 *
 * @property ShopifyRecurringApplicationCharge recurring_charge
 * @property Customer customer
 * @property ShopifyShop shop
 * @property Plan plan
 */
class MonthlyCharge extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_recurring_charge_id = 'recurring_charge_id';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_plan_id = 'plan_id';
    const COLUMN_month = 'month';
    const COLUMN_year = 'year';
    const COLUMN_fixed_price = 'fixed_price';
    const COLUMN_usage_charges = 'usage_charges';
    const COLUMN_usage_charges_billed = 'usage_charges_billed';
    const COLUMN_affiliate_notified = 'affiliate_notified';
    const COLUMN_paid_email_count = 'paid_email_count';

    protected $casts = [
        self::COLUMN_usage_charges_billed => "boolean",
        self::COLUMN_affiliate_notified => "boolean",
    ];

    static function createMonthlyCharges(ShopifyRecurringApplicationCharge $charge)
    {
        $end_date = Carbon::now();
        $start_date = $charge->created_at;
        $date = $start_date->clone();

        while (true) {
            $mc = self::findMonthlyCharge($charge, $date->month, $date->year);
            if (!$mc)
                self::createMonthlyCharge($charge, $date->month, $date->year);

            $date = $date->addMonth();
            if ($date->year == $end_date->year && $date->month >= $end_date->month)
                break;
        }
    }

    static function createMonthlyCharge(ShopifyRecurringApplicationCharge $charge, $month, $year): self
    {
        $model = new self();
        $model->recurring_charge_id = $charge->id;
        $model->customer_id = $charge->customer_id;
        $model->shop_id = $charge->shop_id;
        $model->plan_id = $charge->plan_id;
        $model->month = $month;
        $model->year = $year;
        $model->fixed_price = $charge->price;
        $model->usage_charges = 0;
        $model->usage_charges_billed = false;
        $model->affiliate_notified = false;
        $model->paid_email_count = 0;
        $model->save();
        $model->calculateUsageCharges();

        return $model;
    }

    static function findChargesToBill()
    {
        $date = Carbon::now()->subDays(10);
        return self::query()
            ->where('created_at', "<=", $date)
            ->where(self::COLUMN_usage_charges_billed, false)
            ->get();
    }

    static function findChargesToSendToAffiliates()
    {
        $date = Carbon::now()->subDays(45);
        return self::query()
            ->where('created_at', "<=", $date)
            ->where(self::COLUMN_affiliate_notified, false)
            ->get();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    static function findMonthlyCharge(ShopifyRecurringApplicationCharge $charge, $month, $year): ?self
    {
        return self::query()
            ->where(self::COLUMN_customer_id, $charge->customer_id)
            ->where(self::COLUMN_month, $month)
            ->where(self::COLUMN_year, $year)
            ->first();
    }

    function calculateUsageCharges()
    {
        /** @var ShopifyUsageChargeEntry[] $usage_charge_entries */
        $usage_charge_entries = ShopifyUsageChargeEntry::findUsageEntries($this->customer, $this->month, $this->year);
        $free_emails = $this->customer->getMaxFreeEmails();

        $paid_email_count = 0;
        $usage_charges = 0;
        foreach ($usage_charge_entries as $entry) {
            if ($free_emails) $free_emails--;
            else {
                $paid_email_count += 1;
                $usage_charges += $entry->usage_charge;
            }
            $entry->charged = true;
            $entry->save();
        }
        $this->paid_email_count = $paid_email_count;
        $this->usage_charges = $usage_charges;
        $this->save();
    }

    function getUsageChargeDescription(): string
    {
        $c = Carbon::createFromDate($this->year, $this->month, 1);

        $count = $this->paid_email_count;
        $month = $c->monthName;
        $year = $c->year;

        return "Charges for $count mails in the month of $month $year";
    }

    function billUsageCharge()
    {
        $helper = new ShopifyHelper($this->shop);
        $helper->createUsageCharge($this->recurring_charge, $this);
    }

    function getTotal(): float
    {
        return $this->fixed_price + $this->usage_charges;
    }

    function sendToAffiliate()
    {
        $affiliate_id = $this->customer->user->affiliation_id;
        if (!$affiliate_id) {
            $this->affiliate_notified = 1;
            $this->save();
        }

        try {
            $client = new Client();
            $client->request('GET', "https://affiliate.emailwish.com/integration/addOrder", [
                'query' => [
                    "order_id" => $this->uid,
                    "order_currency" => "USD",
                    "order_total" => $this->getTotal(),
                    "product_ids" => $this->plan_id,
                    "af_id" => $affiliate_id,
                    "ip" => "1.1.1.1",
                    "base_url" => base64_encode("https://".$this->shop->primary_domain),
                    "customFields" => json_encode([]),
                    "script_name" => "php_library",
                ]
            ]);
            $this->affiliate_notified = 1;
            $this->save();
        } catch (RequestException $exception) {

        }
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

    function recurring_charge(): BelongsTo
    {
        return $this->belongsTo(ShopifyRecurringApplicationCharge::class, self::COLUMN_recurring_charge_id);
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, self::COLUMN_customer_id);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class, self::COLUMN_shop_id);
    }

    function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, self::COLUMN_plan_id);
    }
}
