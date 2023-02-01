<?php

namespace Acelle\Model;

use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\DynamicWidgetConfig\OrderConfig;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Acelle\ShopifyModel\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Acelle\Helpers\ShopifyHelper;

/**
 * @property integer id
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property string uid
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property integer shopify_customer_id
 * @property ShopifyCustomer shopify_customer
 * @property string data
 * @property string total_price
 * @property string shopify_id
 * @property string shopify_order_id
 * @property double shopify_total_price
 * @property string|Carbon|null shopify_created_at
 * @property string|Carbon|null shopify_updated_at
 * @property string shopify_fulfillment_status
 * @property boolean from_emailwish
 * @property boolean from_emailwish_popup
 * @property boolean from_emailwish_popup_id
 * @property boolean from_emailwish_chat
 * @property boolean from_emailwish_mail
 */
class ShopifyOrder extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_customer_id = 'shopify_customer_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_shopify_total_price = 'shopify_total_price';
    const COLUMN_shopify_created_at = 'shopify_created_at';
    const COLUMN_shopify_updated_at = 'shopify_updated_at';
    const COLUMN_shopify_fulfillment_status = 'shopify_fulfillment_status';
    const COLUMN_data = 'data';
    const COLUMN_from_emailwish = 'from_emailwish';
    const COLUMN_from_emailwish_popup = 'from_emailwish_popup';
    const COLUMN_from_emailwish_popup_id = 'from_emailwish_popup_id';
    const COLUMN_from_emailwish_chat = 'from_emailwish_chat';
    const COLUMN_from_emailwish_mail = 'from_emailwish_mail';

    protected $hidden = ['data'];

    protected $dates = [
        self::COLUMN_shopify_created_at,
        self::COLUMN_shopify_updated_at,
    ];

    function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    function shop()
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    public function shopify_customer(): BelongsTo
    {
        return $this->belongsTo(ShopifyCustomer::class);
    }

    static function getSalesBreakdown(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'end_date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
        ]);

        $end_date = Carbon::tomorrow();
        if (!empty($data['end_date']))
            $end_date = Carbon::parse($data['end_date'])->addDay();

        $start_date = $end_date->copy()->subDays(45);
        if (!empty($data['start_date']))
            $start_date = Carbon::parse($data['start_date']);
        if ($start_date > $end_date)
            throw ValidationException::withMessages([
                'start_date' => "Start date must not be greater than end date."
            ]);

        return [
            'sales_total' => self::getTotalRevenue($customer, $start_date, $end_date),
            'sales_total_from_emailwish' => self::getTotalRevenue($customer, $start_date, $end_date, "emailwish"),
            'sales_total_from_popups' => self::getTotalRevenue($customer, $start_date, $end_date, "popup"),
            'sales_total_from_chats' => self::getTotalRevenue($customer, $start_date, $end_date, "chat"),
            'sales_total_from_emails' => self::getTotalRevenue($customer, $start_date, $end_date, "mail"),
            'number_of_sales' => self::getTotalCount($customer, $start_date, $end_date),
            'number_of_sales_from_emailwish' => self::getTotalCount($customer, $start_date, $end_date, "emailwish"),
            'number_of_sales_from_popups' => self::getTotalCount($customer, $start_date, $end_date, "popup"),
            'number_of_sales_from_chats' => self::getTotalCount($customer, $start_date, $end_date, "chat"),
            'number_of_sales_from_emails' => self::getTotalCount($customer, $start_date, $end_date, "mail"),
        ];
    }

    static function getRevenueReport(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'end_date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
        ]);

        $end_date = Carbon::tomorrow();
        if (!empty($data['end_date']))
            $end_date = Carbon::parse($data['end_date'])->addDay();

        $start_date = $end_date->copy()->subDays(45);
        if (!empty($data['start_date']))
            $start_date = Carbon::parse($data['start_date']);
        if ($start_date > $end_date)
            throw ValidationException::withMessages([
                'start_date' => "Start date must not be greater than end date."
            ]);

        $now = Carbon::now();
        $h24_ago = $now->clone()->subDay();
        $h48_ago = $h24_ago->clone()->subDay();

        $revenue_total = self::getTotalRevenue($customer, $start_date, $end_date, "emailwish");
        $revenue_last_24_hours = self::getTotalRevenue($customer, $h24_ago, $now, "emailwish");
        $revenue_last_48_to_24_hours = self::getTotalRevenue($customer, $h48_ago, $h24_ago, "emailwish");
        $revenue_change_last_24 = growth_rate($revenue_last_24_hours, $revenue_last_48_to_24_hours);

        $count_total = self::getTotalCount($customer, $start_date, $end_date, "emailwish");
        $count_last_24_hours = self::getTotalCount($customer, $h24_ago, $now, "emailwish");
        $count_last_48_to_24_hours = self::getTotalCount($customer, $h48_ago, $h24_ago, "emailwish");
        $count_change_last_24 = growth_rate($count_last_24_hours, $count_last_48_to_24_hours);

        $cost_total = ShopifyUsageChargeEntry::getTotalExpense($customer, $start_date, $end_date);
        $cost_last_24_hours = ShopifyUsageChargeEntry::getTotalExpense($customer, $h24_ago, $now);
        $cost_last_48_to_24_hours = ShopifyUsageChargeEntry::getTotalExpense($customer, $h48_ago, $h24_ago);
        $cost_change_last_24 = growth_rate($cost_last_24_hours, $cost_last_48_to_24_hours);

        $roi_total = $cost_total ? max(0, (100 * ($revenue_total - $cost_total) / $cost_total)) : null;
        $roi_last_24_hours = $cost_last_24_hours ? max(0, (100 * ($revenue_last_24_hours - $cost_last_24_hours) / $cost_last_24_hours)) : null;
        $roi_last_48_to_24_hours = $count_last_48_to_24_hours ? max(0, (100 * ($revenue_last_48_to_24_hours - $count_last_48_to_24_hours)) / $count_last_48_to_24_hours) : null;
        $roi_change_last_24 = growth_rate($roi_last_24_hours, $roi_last_48_to_24_hours);

        return [
            'revenue_total' => $revenue_total,
            'revenue_last_24_hours' => $revenue_last_24_hours,
            'revenue_last_48_to_24_hours' => $revenue_last_48_to_24_hours,
            'revenue_change_last_24' => $revenue_change_last_24,

            'count_total' => $count_total,
            'count_last_24_hours' => $count_last_24_hours,
            'count_last_48_to_24_hours' => $count_last_48_to_24_hours,
            'count_change_last_24' => $count_change_last_24,

            'cost_total' => $cost_total,
            'cost_last_24_hours' => $cost_last_24_hours,
            'cost_last_48_to_24_hours' => $cost_last_48_to_24_hours,
            'cost_change_last_24' => $cost_change_last_24,

            'roi_total' => $roi_total,
            'roi_last_24_hours' => $roi_last_24_hours,
            'roi_last_48_to_24_hours' => $roi_last_48_to_24_hours,
            'roi_change_last_24' => $roi_change_last_24,
        ];

    }

    static function getTotalRevenue($customer, $start_date, $end_date, $emailwish_filter = null)
    {
        $query = self::query()->where(self::COLUMN_customer_id, $customer->id);
        switch ($emailwish_filter) {
            case "popup":
                $query->where(self::COLUMN_from_emailwish_popup, 1);
                break;
            case "chat":
                $query->where(self::COLUMN_from_emailwish_chat, 1);
                break;
            case "mail":
                $query->where(self::COLUMN_from_emailwish_mail, 1);
                break;
            case "emailwish":
                $query->where(self::COLUMN_from_emailwish, 1);
                break;
        }

        if ($end_date)
            $query->where(self::COLUMN_shopify_created_at, '<=', $end_date);
        if ($start_date)
            $query->where(self::COLUMN_shopify_created_at, '>=', $start_date);

        return $query->sum(self::COLUMN_shopify_total_price) + 0; // Add 0 to force it to return number
    }

    static function getTotalCount($customer, $start_date, $end_date, $emailwish_filter = null): int
    {
        $query = self::query()->where(self::COLUMN_customer_id, $customer->id);
        switch ($emailwish_filter) {
            case "popup":
                $query->where(self::COLUMN_from_emailwish_popup, 1);
                break;
            case "chat":
                $query->where(self::COLUMN_from_emailwish_chat, 1);
                break;
            case "mail":
                $query->where(self::COLUMN_from_emailwish_mail, 1);
                break;
            case "emailwish":
                $query->where(self::COLUMN_from_emailwish, 1);
                break;
        }

        if ($end_date)
            $query->where(self::COLUMN_shopify_created_at, '<=', $end_date);
        if ($start_date)
            $query->where(self::COLUMN_shopify_created_at, '>=', $start_date);

        return $query->count();
    }

    static function getDailyReport(Request $request, Customer $customer, bool $from_emailwish = false)
    {
        $data = $request->validate([
            'end_date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
        ]);

        $end_date = Carbon::tomorrow();
        if (!empty($data['end_date']))
            $end_date = Carbon::parse($data['end_date'])->addDay();

        $start_date = $end_date->copy()->subDays(45);
        if (!empty($data['start_date']))
            $start_date = Carbon::parse($data['start_date']);
        if ($start_date > $end_date)
            throw ValidationException::withMessages([
                'start_date' => "Start date must not be greater than end date."
            ]);

        $diffinDays = $end_date->diffInDays($start_date);

        if ($diffinDays <= 45) {
            $mysql_format = '%Y-%m-%d';
            $php_format = 'Y-m-d';
        } else if ($diffinDays > 45 and $diffinDays <= 365) {
            $mysql_format = '%v week of %x';
            $php_format = 'W-Y';
        } else if ($diffinDays > 365 and $diffinDays <= 1095) {
            $mysql_format = '%Y-%m';
            $php_format = 'Y-m';
        } else {
            $mysql_format = '%Y';
            $php_format = 'Y';
        }

        $query = self::selectRaw("date_format(shopify_created_at, '${mysql_format}') formatted_date, date(min(shopify_created_at)) date, sum(shopify_total_price) total_price, count(*) number_of_orders")
            ->where(self::COLUMN_customer_id, $customer->id)
            ->groupBy('formatted_date');

        if ($from_emailwish)
            $query->where('from_emailwish', 1);

        if ($end_date)
            $query->where(self::COLUMN_shopify_created_at, '<=', $end_date);
        if ($start_date)
            $query->where(self::COLUMN_shopify_created_at, '>=', $start_date);

        // dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
        $results = $query->get();
        $date_wise_results = [];
        foreach ($results as $result) {
            $date_wise_results[$result['formatted_date']] = $result;
        }

        $result_date = $start_date->copy();
        do {
            $formatted_date = $result_date->format($php_format);
            if ($diffinDays > 45 and $diffinDays <= 365) {
                $formatted_date = str_replace('-', ' week of ', $formatted_date);
            }
            if (empty($date_wise_results[$formatted_date]))
                $date_wise_results[$formatted_date] = [
                    'formatted_date' => $formatted_date,
                    'date' => $result_date->format("Y-m-d"),
                    'total_price' => 0,
                    'number_of_orders' => 0
                ];
            $result_date = $result_date->addDay();
        } while ($result_date < $end_date);
        usort($date_wise_results, function ($a, $b) {
            return ($a['formatted_date'] ?? '') <=> ($b['formatted_date'] ?? '');
        });

        $total_sales = 0;
        $total_orders = 0;
        foreach ($date_wise_results as $result) {
            $total_sales += $result['total_price'] ?? 0;
            $total_orders += $result['number_of_orders'] ?? 0;
        }

        return [
            'total_sales' => $total_sales,
            'total_orders' => $total_orders,
            'items' => array_values($date_wise_results)
        ];
    }

    static function findByShopifyId(string $shopify_id): ?self
    {
        return self::where(self::COLUMN_shopify_id, $shopify_id)->first();
    }

    static function findByShopAndId(ShopifyShop $shop, string $shopify_id): ?self
    {
        return self::where(self::COLUMN_shop_id, $shop->id)
            ->where(self::COLUMN_shopify_id, $shopify_id)
            ->first();
    }

    /**
     * @param ShopifyShop $shop
     * @param array $shopify_ids
     * @return self[]
     */
    static function findByShopAndIds(ShopifyShop $shop, array $shopify_ids): array
    {
        return self::where(self::COLUMN_shop_id, $shop->id)
            ->where(self::COLUMN_shopify_id, $shopify_ids)
            ->get();
    }

    static function findByShopAndIdOrCreate(ShopifyShop $shop, string $shopify_id): ?self
    {
        $model = new self();
        $model->shopify_id = $shopify_id;
        $model->shop_id = $shop->id;
        $model->customer_id = $shop->customer_id;
        $model->data = "[]";
        $model->shopify_total_price = 0;
        $model->shopify_created_at = Carbon::now();
        $model->shopify_updated_at = Carbon::now();
        $model->from_emailwish = false;
        $model->from_emailwish_popup = false;
        $model->from_emailwish_chat = false;
        $model->from_emailwish_mail = false;

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }

    static function storeOrder(ShopifyShop $shop, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $shopify_customer = ShopifyCustomer::storeCustomer($shop, $data['customer'] ?? []);
        if (!$shopify_customer) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->shopify_customer_id = $shopify_customer->id;
        $model->shopify_total_price = $model->getShopifyModel()->total_price;
        $model->shopify_created_at = Carbon::parse($model->getShopifyModel()->created_at)->setTimezone('UTC');
        $model->shopify_updated_at = Carbon::parse($model->getShopifyModel()->updated_at)->setTimezone('UTC');
        $model->shopify_fulfillment_status = $model->getShopifyModel()->fulfillment_status ?? "";

        $model->setFromEmailwish();
        $model->save();
        if ($model->wasRecentlyCreated)
            $model->updateProductUnitsSoldAttribute();
        return $model;
    }

    function updateProductUnitsSoldAttribute()
    {
        $items = $this->getShopifyModel()->line_items ?? [];
        foreach ($items as $item) {
            $product = ShopifyProduct::findByShopifyId($item->product_id);
            if ($product) {
                $product->units_sold = $product->units_sold + ($item->quantity ?? 0);
                $product->save();
            }
        }
    }

    function setFromEmailwish()
    {
        $order_date = $this->shopify_created_at->clone();
        $two_days_ago = $order_date->clone()->subDays(2);
        $thirty_days_ago = $order_date->clone()->subDays(30);

        /** @var PopupResponse $popup_response */
        $popup_response = PopupResponse::query()
            ->where(PopupResponse::COLUMN_customer_id, $this->customer_id)
            ->where(PopupResponse::COLUMN_email, $this->shopify_customer->email)
            ->where(PopupResponse::COLUMN_created_at, ">=", $two_days_ago)
            ->where(PopupResponse::COLUMN_created_at, "<=", $order_date)
            ->orderBy(PopupResponse::COLUMN_created_at, 'desc')
            ->first();

        $chat_messages_count = ChatSession::query()
            ->where(ChatSession::COLUMN_customer_id, $this->customer_id)
            ->where(ChatSession::COLUMN_email, $this->shopify_customer->email)
            ->where(ChatSession::COLUMN_last_message_at, ">=", $two_days_ago)
            ->where(ChatSession::COLUMN_last_message_at, "<=", $order_date)
            ->count();

        $mails_count = TrackingLog::query()
            ->where(TrackingLog::COLUMN_customer_id, $this->customer_id)
            ->where(TrackingLog::COLUMN_subscriber_id, $this->shopify_customer->subscriber_id)
            ->where(TrackingLog::COLUMN_created_at, ">=", $thirty_days_ago)
            ->where(TrackingLog::COLUMN_created_at, "<=", $order_date)
            ->count();

        if ($popup_response) {
            $this->from_emailwish_popup = true;
            $this->from_emailwish_popup_id = $popup_response->popup_id;
        } else if ($chat_messages_count > 0) {
            $this->from_emailwish_chat = true;
        } else if ($mails_count > 0) {
            $this->from_emailwish_mail = true;
        }

        $this->from_emailwish = $this->from_emailwish_mail || $this->from_emailwish_chat || $this->from_emailwish_popup;
    }

    /**
     * @return Order
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
        return self::where('uid', '=', $uid)->first();
    }

    public static function boot()
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

    static function prepareOrderEmail(?self $instance, $html)
    {
        $find = '<tbody class="abandoned-body-starts';
        $start = 0;
        while (true) {
            $index = strpos($html, $find, $start);
            if (!$index)
                break;
            $index1 = strpos($html, '>', $index + strlen($find));
            $a = substr($html, 0, $index1 + 1);

            $isDescriptionIndex = strpos($html, 'data-isdescription', $index + strlen($find));
            if (!$isDescriptionIndex) {
                $start = $index + strlen($find);
                continue;
            }
            list($d1, $d2, $isDescription) = ShopifyProduct::getBetweenData($html, $isDescriptionIndex + strlen('data-isdescription'), '"', '"');
            if ($d1 == -1 || $d2 == -1) {
                $start = $index + strlen($find);
                continue;
            }
            $isDescription = (int)$isDescription;

            list($a1, $a2, $data1) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1', $index + strlen($find)) + strlen('abandoned-body-starts-r1'), '"', '"');
            list($a1, $a2, $data2) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1'), '"', '"');
            list($a1, $a2, $data3) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item'), '"', '"');
            list($a1, $a2, $data4) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-table', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-table'), '"', '"');
            list($a1, $a2, $data5) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1'), '"', '"');
            list($a1, $a2, $data6) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c1'), '"', '"');
            list($a1, $a2, $data7) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c1-img', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c1-img'), '"', '"');
            list($a1, $a2, $data8) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2'), '"', '"');
            list($a1, $a2, $data9) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-title', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-title'), '"', '"');
            list($a1, $a2, $data10) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-info', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-info'), '"', '"');
            list($a1, $a2, $data11) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-price', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-price'), '"', '"');
            list($a1, $a2, $data12) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c2', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c2'), '"', '"');
            list($a1, $a2, $data13) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c2-qty', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c2-qty'), '"', '"');
            list($a1, $a2, $data14) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c3', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c3'), '"', '"');
            list($a1, $a2, $data15) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c3-price', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c3-price'), '"', '"');

            $x = '';
            if ($instance) {
                foreach ($instance->getShopifyModel()->line_items as $item) {
                    $product = ShopifyProduct::findByShopifyId($item->product_id);
                    $model = $product->getShopifyModel();
                    $image_url_1 = '';
                    foreach ($model->variants as $v) {
                        if ($v->id == $item->variant_id) {
                            foreach ($model->images as $img) {
                                if ($img->id == $v->image_id) {
                                    $image_url_1 = $img->src;
                                    break;
                                }
                            }
                            break;
                        }
                    }
                    $description_1 = ShopifyHelper::html2Text($model->body_html);
                    $descHtml = '';
                    if ($isDescription) {
                        $descHtml = '<div class="item-info" style="' . $data10 . '">' . $description_1 . '</div>';
                    }

                    $x .= '<tr style="' . $data1 . '">' .
                        '<td style="' . $data2 . '">' .
                        '<div class="item" style="' . $data3 . '">' .
                        '<table style="' . $data4 . '">' .
                        '<tr style="' . $data5 . '">' .
                        '<td style="' . $data6 . '"><img class="no-image-edit" src="' . $image_url_1 . '" width="100px" style="' . $data7 . '">' .
                        '</td>' .
                        '<td class="mobile-info" style="' . $data8 . '">' .
                        '<div class="item-title" style="' . $data9 . '">' . $item->title . ' - ' . $item->variant_title . '</div>' .
                        $descHtml .
                        '<div class="mobile-qty-price" style="' . $data11 . '">' . $item->quantity . 'x' . $item->price_set->presentment_money->currency_code . ' ' . $item->price_set->presentment_money->amount . '</div>' .
                        '</td>' .
                        '</tr>' .
                        '</table>' .
                        '</div>' .
                        '</td>' .
                        '<td style="' . $data12 . '">' .
                        '<div class="qty" contenteditable="false" style="' . $data13 . '">' . $item->quantity . '</div>' .
                        '</td>' .
                        '<td style="' . $data14 . '">' .
                        '<div class="price" contenteditable="false" style="' . $data15 . '">' . $item->price_set->presentment_money->currency_code . ' ' . $item->price_set->presentment_money->amount . '</div>' .
                        '</td>' .
                        '</tr>';
                }
            }
            $a .= $x;
            $a .= substr($html, $index1 + 1);
            $html = $a;

            $start = $index1;
            $footerFind = '<div class="footer';
            $fIndex = strpos($html, $footerFind, $start);
            $orderModel = $instance->getShopifyModel();
            if ($fIndex) {
                $spanStart = $fIndex + strlen($footerFind);
                for ($i = 0; $i < 4; $i++) {
                    $spanIndex = strpos($html, '<span', $spanStart);
                    if ($spanIndex) {
                        list($a1, $b1, $count) = ShopifyProduct::getBetweenData($html, $spanIndex + strlen('<span'), '>', '<');
                        if ($a1 == -1 || $b1 == -1) {
                            $spanStart = $spanIndex + strlen('<span');
                            continue;
                        }
                        $val = '';
                        if ($i == 0) {
                            $val = $orderModel->current_subtotal_price_set->presentment_money->currency_code . ' ' . $orderModel->current_subtotal_price_set->presentment_money->amount;
                        } else if ($i == 1) {
                            $val = $orderModel->current_total_tax_set->presentment_money->currency_code . ' ' . $orderModel->current_total_tax_set->presentment_money->amount;
                        } else if ($i == 2) {
                            $val = $orderModel->total_shipping_price_set->presentment_money->currency_code . ' ' . $orderModel->total_shipping_price_set->presentment_money->amount;
                        } else if ($i == 3) {
                            $val = $orderModel->current_total_price_set->presentment_money->currency_code . ' ' . $orderModel->current_total_price_set->presentment_money->amount;
                        }
                        $html = substr($html, 0, $a1 + 1) . $val . substr($html, $b1);
                        $spanStart = $spanIndex + strlen('<span');
                    }
                }
            }

        }
        return $html;
    }


    public static function prepareReviewEmail($html, ShopifyFulfillment $order)
    {
        $find = '<div class="submit-review-product';
        $endFind = '<!--- end-submit-review-product -->';
        $start = 0;
        while (true) {
            $index = strpos($html, $find, $start);
            if (!$index) {
                break;
            }
            $endIndex = strpos($html, $endFind, $index + strlen($find));
            if (!$endIndex) {
                break;
            }

            $data = $order->getShopifyModel();

            $products = '';
            foreach ($data->line_items as $product) {
                $prd = ShopifyProduct::findByShopifyId($product->product_id);
                if (!$prd) {
                    continue;
                }
                $model = $prd->getShopifyModel();
                $imgSrc = ($model->images && count($model->images)) ? $model->images[0]->src : "";

                $products .= '<div class="submit-review-product row clearfix" style="text-align: center;">';
                $products .= '	<div class="column full">';
                $products .= '		<img src="' . $imgSrc . '" alt="" loading="lazy">';
                $products .= '		<h1 style="font-size: 28px; font-weight: 700;">How did you like this item?</h1>';
                $products .= '		<h2 style="font-size: 22px; color: rgb(102, 102, 102); font-weight: 500;">' . $product->name . '</h2>';
                $products .= '		<a href="' . $prd->getProductLink() . '" title="">';
                $products .= '			<img src="' . \Illuminate\Support\Facades\URL::asset("/") . '/cb/assets/minimalist-blocks/images/0_stars.png" alt="" loading="lazy" style="width: 60%; height: 10%;">';
                $products .= '		</a>';
                $products .= '	</div>';
                $products .= '</div>';
            }

            $html = substr($html, 0, $index) . $products . substr($html, $endIndex + strlen($endFind));

            $start += $index + strlen($find);
        }
        return $html;
    }

    static function getHtmlRepresentation($instance, OrderConfig $config)
    {
        return '
    <div style="background-color:' . $config->text_background_color . ';color:' . $config->text_color . '">
    <div class="row clearfix">
        <div class="column full" style="position: relative;">
            <p>Order Summary</p>
            <p style="font-style: normal; top: 10px; right: 10px; float: right; position: absolute;">
            </p>
        </div>
    </div>
    <div class="row clearfix">
        <div class="column full">
            <table class="default table-condensed"
                   style="table-layout:fixed;border-collapse:collapse;width:100%;border-spacing: 0;">
                <tbody>
                <tr>
                    <td style="width:50px"></td>
                    <td colspan="3">
                        <p class="margin-0"><b>Test Title x 1 </b><br>Variant</p>
                    </td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$41.55</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Subtotal</td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$41.55</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Shipping</td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$0.00</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Taxes</td>
                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$0.00</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td>Total</td>
                    <td colspan="2"><p class="margin-0" style="text-align:right;font-size:1.5em;"><b>$41.55</b></p></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
';
    }


    function getFirstProductLink(): string
    {
        $product_id = $this->getShopifyModel()->line_items[0]->product_id ?? null;
        if (!$product_id) return "";

        $product = ShopifyProduct::findByShopifyId($product_id);
        if (!$product) return "";

        return "https://" . $this->shop->primary_domain . "/products/" . $product->getShopifyModel()->handle;
    }

    function getFirstProductTitle(): string
    {
        $product_id = $this->getShopifyModel()->line_items[0]->product_id ?? null;
        if (!$product_id) return "";

        $product = ShopifyProduct::findByShopifyId($product_id);
        if (!$product) return "";

        return $product->shopify_title;
    }

    function getFirstDiscountCode(): string
    {
        $o = $this->getShopifyModel();
        if (!count($o->discount_codes)) return "";
        return $o->discount_codes[0]->code;
    }

    function getFirstDiscountAmount(): string
    {
        $o = $this->getShopifyModel();
        if (!count($o->discount_codes)) return "";
        return $o->discount_codes[0]->amount;
    }

    function trigerAutomationOrderPlaced()
    {
        $context = new ShopifyAutomationContext();
        $context->shopifyCustomer = $this->shopify_customer;
        $context->shopifyOrder = $this;

        $automation2s = Automation2::findByTrigger($this->customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED);
        foreach ($automation2s as $automation2) {
            $automation2->logger()->info(sprintf('Queuing automation "%s" in response to webhook', $automation2->name));
            dispatch(new RunAutomationWithContext($automation2, $context));
        }
    }

    function trigerAutomationOrderFulfilled()
    {
        $context = new ShopifyAutomationContext();
        $context->shopifyCustomer = $this->shopify_customer;
        $context->shopifyOrder = $this;

        $automation2s = Automation2::findByTrigger($this->customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED);
        foreach ($automation2s as $automation2) {
            $automation2->logger()->info(sprintf('Queuing automation "%s" in response to webhook', $automation2->name));
            dispatch(new RunAutomationWithContext($automation2, $context));
        }
    }

    function triggerAdminAutomationEmailwishOrderPlaced()
    {
        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->customer->user->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->shopifyOrder = $this;
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }
}
