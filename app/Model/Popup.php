<?php

namespace Acelle\Model;

use Acelle\Http\Controllers\PopupController;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManagerStatic;
use Symfony\Component\Mime\MimeTypes;

/**
 * Class Popup
 * @package Acelle\Model
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string uid
 * @property integer customer_id
 * @property Customer customer
 * @property boolean active
 * @property boolean active_mobile
 * @property string title
 * @property string data
 * @property array triggers
 * @property integer height
 * @property integer width
 * @property string type
 * @property string popup_position
 * @property string thumbnail_file_path
 * @property string behaviour
 * @property bool default_for_new_customers
 *
 * @property PopupResponse responses
 */
class Popup extends Model
{
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_active = 'active';
    const COLUMN_active_mobile = 'active_mobile';
    const COLUMN_title = 'title';
    const COLUMN_data = 'data';
    const COLUMN_triggers = 'triggers';
    const COLUMN_height = 'height';
    const COLUMN_width = 'width';
    const COLUMN_type = 'type';
    const COLUMN_popup_position = 'popup_position';
    const COLUMN_thumbnail_file_path = 'thumbnail_file_path';
    const COLUMN_behaviour = 'behaviour';
    const COLUMN_default_for_new_customers = 'default_for_new_customers';

    const PROPERTY_thumbnail_file = 'thumbnail_file';
    const ACCEPTED_EXTENSIONS = 'jpg,jpeg,png';

    const TRIGGER_TYPES = [
        "on-load",
        "scroll-start",
        "scroll-middle",
        "scroll-end",
        "idle",
        "leaving",
    ];

    const BEHAVIOUR_once = 'once';
    const BEHAVIOUR_till_submission = 'till_submission';

    const BEHAVIOURS = [
        self::BEHAVIOUR_once,
        self::BEHAVIOUR_till_submission
    ];

    protected $appends = [
        "responses_count",
        "impression_count",
        "order_count",
        "revenue",
        "click_through_rate",
        "conversion_rate",
        "thumbnail_full_url"
    ];

    protected $casts = [
        self::COLUMN_active => "bool",
        self::COLUMN_active_mobile => "bool",
        self::COLUMN_triggers => "array",
        self::COLUMN_default_for_new_customers => "bool"
    ];

    function getResponsesCountAttribute(): int
    {
        return $this->responses()->count();
    }

    function getImpressionCountAttribute(): int
    {
        return $this->impressions()->count();
    }

    function getOrderCountAttribute(): int
    {
        return $this->shopify_orders()->count();
    }

    function getRevenueAttribute()
    {
        return $this->shopify_orders()->sum(ShopifyOrder::COLUMN_shopify_total_price) + 0;
    }

    function getClickThroughRateAttribute()
    {
        $clicks = $this->getResponsesCountAttribute();
        $impressions = $this->getImpressionCountAttribute();

        return $impressions ? (100 * $clicks / $impressions) : 0.0;
    }

    function getConversionRateAttribute()
    {
        $orders = $this->getOrderCountAttribute();
        $clicks = $this->getResponsesCountAttribute();

        return $clicks ? (100 * $orders / $clicks) : 0.0;
    }

    function getThumbnailFullUrlAttribute(): ?string
    {
        if (empty($this->thumbnail_file_path))
            return null;
        // Force cache reload using updated_at timestamp
        $timestamp = $this->updated_at->timestamp;
        return action([PopupController::class, 'thumbnail'], ['popup' => $this->uid]) . '?ts=' . $timestamp;
    }

    static function getSummary(Customer $customer): array
    {
        $now = Carbon::now();
        $h24_ago = $now->clone()->subDay();
        $h48_ago = $h24_ago->clone()->subDay();

        $sales_total = ShopifyOrder::getTotalRevenue($customer, null, null, "popup");
        $sales_total_last_24_hours = ShopifyOrder::getTotalRevenue($customer, $h24_ago, $now, "popup");
        $sales_total_last_48_to_24_hours = ShopifyOrder::getTotalRevenue($customer, $h48_ago, $h24_ago, "popup");
        $sales_total_change_last_24 = growth_rate($sales_total_last_24_hours, $sales_total_last_48_to_24_hours);

        $number_of_sales = ShopifyOrder::getTotalCount($customer, null, null, "popup");
        $number_of_sales_last_24_hours = ShopifyOrder::getTotalCount($customer, $h24_ago, $now, "popup");
        $number_of_sales_48_to_24_hours = ShopifyOrder::getTotalCount($customer, $h48_ago, $h24_ago, "popup");
        $number_of_sales_change_last_24 = growth_rate($number_of_sales_last_24_hours, $number_of_sales_48_to_24_hours);

        $impressions = PopupImpression::getTotalCount($customer, null, null);
        $impressions_last_24_hours = PopupImpression::getTotalCount($customer, $h24_ago, $now);
        $impressions_last_48_to_24_hours = PopupImpression::getTotalCount($customer, $h48_ago, $h24_ago);
        $impressions_change_last_24 = growth_rate($impressions_last_24_hours, $impressions_last_48_to_24_hours);

        $responses = PopupResponse::getTotalCount($customer, null, null);
        $responses_last_24_hours = PopupResponse::getTotalCount($customer, $h24_ago, $now);
        $responses_last_48_to_24_hours = PopupResponse::getTotalCount($customer, $h48_ago, $h24_ago);
        $responses_change_last_24 = growth_rate($responses_last_24_hours, $responses_last_48_to_24_hours);

        return [
            'sales_total' => $sales_total,
            'sales_total_last_24_hours' => $sales_total_last_24_hours,
            'sales_total_last_48_to_24_hours' => $sales_total_last_48_to_24_hours,
            'sales_total_change_last_24' => $sales_total_change_last_24,

            'number_of_sales' => $number_of_sales,
            'number_of_sales_last_24_hours' => $number_of_sales_last_24_hours,
            'number_of_sales_48_to_24_hours' => $number_of_sales_48_to_24_hours,
            'number_of_sales_change_last_24' => $number_of_sales_change_last_24,

            'impressions' => $impressions,
            'impressions_last_24_hours' => $impressions_last_24_hours,
            'impressions_last_48_to_24_hours' => $impressions_last_48_to_24_hours,
            'impressions_change_last_24' => $impressions_change_last_24,

            'responses' => $responses,
            'responses_last_24_hours' => $responses_last_24_hours,
            'responses_last_48_to_24_hours' => $responses_last_48_to_24_hours,
            'responses_change_last_24' => $responses_change_last_24,
        ];
    }

    public function updateTriggerRules()
    {
        return [
            self::COLUMN_behaviour => ['required', 'string', Rule::in(self::BEHAVIOURS)],
            self::COLUMN_active => 'required|boolean',
            self::COLUMN_active_mobile => 'required|boolean',
            self::COLUMN_triggers => 'required|array|min:1',
            self::COLUMN_triggers . '.*.type' => 'required|string|in:' . implode(',', self::TRIGGER_TYPES),
            self::COLUMN_triggers . '.*.delay_seconds' => 'required|integer|min:0|max:999',
        ];
    }

    public function fill_popup(array $data)
    {
        $this->title = $data[self::COLUMN_title] ?? "";
        $this->data = $data[self::COLUMN_data] ?? "";
        $this->height = $data[self::COLUMN_height] ?? 0;
        $this->width = $data[self::COLUMN_width] ?? 0;
        $this->type = $data[self::COLUMN_type] ?? "";
        $this->popup_position = $data[self::COLUMN_popup_position] ?? "";

        return $this;
    }

    function storeThumbnailFromString($thumbnail_base64)
    {
        if (empty($thumbnail_base64)) {
            $this->thumbnail_file_path = "";
            return;
        }
        try {
            $thumbnail_image = ImageManagerStatic::make($thumbnail_base64);

            $mime_provider = MimeTypes::getDefault();
            $mimes = [];
            foreach (explode(',', self::ACCEPTED_EXTENSIONS) as $extension)
                $mimes = array_merge($mimes, $mime_provider->getMimeTypes($extension));

            if (!in_array($thumbnail_image->mime, $mimes)) {
                $this->thumbnail_file_path = "";
                return;
                //throw new Exception("Invalid mime type");
            }
        } catch (Exception $e) {
            $this->thumbnail_file_path = "";
            return;
            /*throw ValidationException::withMessages([
                self::PROPERTY_thumbnail_file => 'Please upload an image of type: ' . self::ACCEPTED_EXTENSIONS,
            ]);*/
        }

        if ($thumbnail_image) {
            $storage_path = 'popup_thumbnail_files/' . Str::random(40) . '.' . $mime_provider->getExtensions($thumbnail_image->mime)[0] ?? 'jpeg';
            Storage::put($storage_path, $thumbnail_image->encode());
            $this->thumbnail_file_path = $storage_path;
        }
    }

    static function validateAndStorePopup(Customer $customer, array $input_data)
    {
        $model = new self();
        $data = custom_validate($input_data, [
            self::COLUMN_title => 'required|string|max:255',
            self::COLUMN_data => 'required|string|max:30000',
            self::COLUMN_height => 'required|integer|min:0|max:9999',
            self::COLUMN_width => 'required|integer|min:0|max:9999',
            self::COLUMN_type => 'required|string|max:255',
            self::COLUMN_popup_position => 'required|string|max:255',
            self::PROPERTY_thumbnail_file => 'nullable|string',
        ]);

        $thumbnail_base64 = $data[self::PROPERTY_thumbnail_file] ?? null;

        $model->storeThumbnailFromString($thumbnail_base64);
        $model->fill_popup($data);
        $model->active = false;
        $model->active_mobile = false;
        $model->triggers = [];

        if (!$customer->popups()->save($model)) return false;
        return $model;
    }

    function validateAndUpdatePopup(array $input_data)
    {
        $data = custom_validate($input_data, [
            self::COLUMN_title => 'required|string|max:255',
            self::COLUMN_data => 'required|string|max:30000',
            self::COLUMN_height => 'required|integer|min:0|max:9999',
            self::COLUMN_width => 'required|integer|min:0|max:9999',
            self::COLUMN_type => 'required|string|max:255',
            self::COLUMN_popup_position => 'required|string|max:255',
            self::PROPERTY_thumbnail_file => 'required|string',
        ]);

        $thumbnail_base64 = $data[self::PROPERTY_thumbnail_file] ?? null;

        $this->storeThumbnailFromString($thumbnail_base64);
        $this->fill_popup($data);
        $this->save();
    }

    public function update_popup_trigger(array $data)
    {
        $this->behaviour = $data[self::COLUMN_behaviour] ?? "";
        $this->active = (bool)($data[self::COLUMN_active] ?? false);
        $this->active_mobile = (bool)($data[self::COLUMN_active_mobile] ?? false);
        $this->triggers = $data[self::COLUMN_triggers] ?? [];

        if (!$this->save()) return false;
        return $this;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shopify_orders(): HasMany
    {
        return $this->hasMany(ShopifyOrder::class, ShopifyOrder::COLUMN_from_emailwish_popup_id);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PopupResponse::class, PopupResponse::COLUMN_popup_id);
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(PopupImpression::class, PopupImpression::COLUMN_popup_id);
    }

    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('popups.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('popups.title', 'like', '%' . $keyword . '%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
        }

        // Other filter
        if (!empty($request->customer_id)) {
            $query = $query->where('popups.customer_id', '=', $request->customer_id);
        }

        return $query;
    }

    /**
     * @param $uid
     * @return self|null
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

    /** @return self[] */
    static function getPublicPopups()
    {
        return self::where(self::COLUMN_default_for_new_customers, 1)->get();
    }


    static function getNewPublicPopupsCount(Customer $customer): int
    {
        $query = self::where(self::COLUMN_default_for_new_customers, 1);
        if ($customer->popups_imported_at != null)
            $query->where(self::COLUMN_updated_at, '>', $customer->popups_imported_at);
        return $query->count();
    }

    function makeCopy($name, Customer $newCustomer)
    {
        $model = new self();
        $model->data = $this->data;
        $model->title = $name ?: $this->title . ' copy';
        $model->active = $this->active;
        $model->active_mobile = $this->active_mobile;
        $model->triggers = $this->triggers;
        $model->width = $this->width;
        $model->height = $this->height;
        $model->type = $this->type;
        $model->popup_position = $this->popup_position;
        $model->thumbnail_file_path = $this->thumbnail_file_path;

        $newCustomer->popups()->save($model);

        if ($newCustomer->id != $this->customer_id) {
            $newCustomer->popups_imported_at = Carbon::now();
            $newCustomer->save();
        }
        return $model;
    }
}
