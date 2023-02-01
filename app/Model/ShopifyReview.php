<?php

namespace Acelle\Model;

use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * @property int id
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property string uid
 * @property int customer_id
 * @property Customer customer
 * @property int shop_id
 * @property ShopifyShop shop
 * @property string shop_name
 * @property int stars
 * @property int product_id
 * @property int shopify_product_id
 * @property ShopifyProduct product
 * @property string reviewer_email
 * @property string reviewer_name
 * @property string title
 * @property string message
 * @property boolean verified_purchase
 * @property boolean approved
 * @property string ip_address
 * @property integer subscriber_id
 * @property Subscriber subscriber
 * @property string secret_key
 *
 * @property ShopifyReviewImage[] images
 */
class ShopifyReview extends Model
{
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shop_name = 'shop_name';
    const COLUMN_stars = 'stars';
    const COLUMN_product_id__DEPRECATED = 'product_id';
    const COLUMN_shopify_product_id = 'shopify_product_id';
    const COLUMN_reviewer_email = 'reviewer_email';
    const COLUMN_reviewer_name = 'reviewer_name';
    const COLUMN_title = 'title';
    const COLUMN_message = 'message';
    const COLUMN_verified_purchase = 'verified_purchase';
    const COLUMN_approved = 'approved';
    const COLUMN_ip_address = 'ip_address';
    const COLUMN_subscriber_id = 'subscriber_id';
    const COLUMN_secret_key = 'secret_key';

    protected $casts = [
        self::COLUMN_approved => 'boolean',
        self::COLUMN_verified_purchase => 'boolean',
    ];

    protected $hidden = [
        self::COLUMN_secret_key
    ];

    protected $with = ['images', 'product'];

    protected $appends = ['product_title'];

    const SORTABLE_COLUMNS = [
        self::COLUMN_created_at,
        self::COLUMN_updated_at,
        self::COLUMN_stars,
        self::COLUMN_reviewer_name,
        self::COLUMN_reviewer_email,
        self::COLUMN_title,
        self::COLUMN_verified_purchase,
        self::COLUMN_ip_address,
        self::COLUMN_subscriber_id,
    ];

    public function getProductTitleAttribute()
    {
        if ($this->product != null) {
            $model = $this->product->getShopifyModel();
            if ($model)
                return $model->title;
        }
        return $this->shopify_product_id;
    }

    static function validateAndCreateReview(ShopifyShop $shop, array $input_array)
    {
        $data = custom_validate($input_array, [
            self::COLUMN_stars => 'required|integer|max:5|min:1',
            self::COLUMN_shopify_product_id => 'required|integer',
            self::COLUMN_reviewer_email => 'required|email|max:100',
            self::COLUMN_reviewer_name => 'required|string|max:100',
            self::COLUMN_title => 'required|string|max:250',
            self::COLUMN_message => 'required|string|max:2000',
            'images' => 'array|min:0|max:10',
            'images.*' => 'file|max:5120|mimes:png,jpg,jpeg',
            self::COLUMN_verified_purchase => 'required|boolean',
        ]);
        $review = new self();

        $review->stars = $data['stars'] ?? 0;
        $review->shopify_product_id = $data['shopify_product_id'] ?? 0;
        $review->reviewer_email = $data['reviewer_email'] ?? "";
        $review->reviewer_name = $data['reviewer_name'] ?? "";
        $review->title = $data['title'] ?? "";
        $review->message = $data['message'] ?? "";
        $review->verified_purchase = $data['verified_purchase'] ?? "";
        $review->approved = false;
        $review->ip_address = request()->ip();
        $review->shop_id = $shop->id;
        $review->shop_name = $shop->name;
        $review->customer_id = $shop->customer->id;
        $review->save();

        $images = $data['images'] ?? [];
        foreach ($images as $image) {
            ShopifyReviewImage::store_image($review, $image);
        }

        return $review;
    }

    function isDuplicateEntry(): bool
    {
        $model = self::query()
            ->where(self::COLUMN_title, $this->title)
            ->where(self::COLUMN_shopify_product_id, $this->shopify_product_id)
            ->where(self::COLUMN_message, $this->message)
            ->where(self::COLUMN_reviewer_name, $this->reviewer_name)
            ->first();
        return $model != null;
    }

    static function getDailyReport(Request $request, Customer $customer)
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
            $php_format = 'W-Y';
        } else {
            $mysql_format = '%Y';
            $php_format = 'Y';
        }

        $query = ShopifyReview::selectRaw("date_format(created_at, '${mysql_format}') formatted_date, date(min(created_at)) date, count(*) number_of_reviews")
            ->where('customer_id', $customer->id)
            ->groupBy('formatted_date');

        $query->where('created_at', '<=', $end_date);
        $query->where('created_at', '>=', $start_date);
        // dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
        $results = $query->get();
        $date_wise_results = [];
        foreach ($results as $result) {
            $date_wise_results[$result['formatted_date']] = $result;
        }

        $result_date = $start_date->copy();
        do {
            $formatted_date = $result_date->format($php_format);
            if($diffinDays > 45 and $diffinDays <= 365){
                $formatted_date = str_replace('-', ' week of ', $formatted_date);
                }
            if (empty($date_wise_results[$formatted_date]))
                $date_wise_results[$formatted_date] = [
                    'formatted_date' => $formatted_date,
                    'date' => $result_date->format("Y-m-d"),
                    'number_of_reviews' => 0
                ];
            $result_date = $result_date->addDay();
        } while ($result_date < $end_date);
        usort($date_wise_results, function ($a, $b) {
            return ($a['formatted_date'] ?? '') <=> ($b['formatted_date'] ?? '');
        });

        $total_reviews_with_timeframe = 0;
        foreach ($date_wise_results as $result) {
            $total_reviews_with_timeframe += $result['number_of_reviews'] ?? 0;
        }

        $total_reviews = ShopifyReview::where('customer_id', $customer->id)->count();
        return [
            'total_reviews' => $total_reviews,
            'total_reviews_within_timeframe' => $total_reviews_with_timeframe,
            'items' => array_values($date_wise_results)
        ];
    }

    static function validateAndCreateReviewForGuest(ShopifyShop $shop, array $input_array, $allow_image_urls = false)
    {
        $message_required = $allow_image_urls ? "nullable" : "required";
        $data = custom_validate($input_array, [
            self::COLUMN_stars => 'required|integer|max:5|min:1',
            self::COLUMN_shopify_product_id => 'required|integer',
            self::COLUMN_reviewer_email => 'nullable|email|max:100',
            self::COLUMN_reviewer_name => 'required|string|max:100',
            self::COLUMN_title => 'nullable|string|max:250',
            self::COLUMN_message => $message_required . '|string|max:2000',
            'images' => 'array|min:0|max:10',
            'images.*' => 'required|file|max:5120|mimes:png,jpg,jpeg',
            'image_urls' => 'array|min:0|max:10',
            'image_urls.*' => 'nullable|string|url|max:5120'
        ]);
        $review = new self();

        $review->stars = $data['stars'] ?? 0;
        $review->shopify_product_id = $data['shopify_product_id'] ?? 0;
        $review->reviewer_email = $data['reviewer_email'] ?? "";
        $review->reviewer_name = $data['reviewer_name'] ?? "";
        $review->title = $data['title'] ?? "";
        $review->message = $data['message'] ?? "";
        $review->verified_purchase = $data['verified_purchase'] ?? "";
        $review->approved = true;
        $review->ip_address = request()->ip();
        $review->shop_id = $shop->id;
        $review->shop_name = $shop->name;
        $review->customer_id = $shop->customer->id;

        if (!$review->isDuplicateEntry()) {
            $review->save();
            $images = $data['images'] ?? [];
            foreach ($images as $image) {
                ShopifyReviewImage::store_image($review, $image);
            }
            if ($allow_image_urls) {
                $image_urls = $data['image_urls'] ?? [];
                foreach ($image_urls as $url) {
                    if (!empty($url))
                        ShopifyReviewImage::store_image_from_url($review, $url);
                }
            }
            $review->triggerCreationAutomations();
            return response('added', 200);
        }

        return response('duplicate', 200);
    }

    function validateAndUpdateForGuest($input_data)
    {
        $data = custom_validate($input_data, [
            self::COLUMN_stars => 'required|integer|max:5|min:1',
            self::COLUMN_reviewer_email => 'required|email|max:100',
            self::COLUMN_reviewer_name => 'required|string|max:100',
            self::COLUMN_title => 'required|string|max:250',
            self::COLUMN_message => 'required|string|max:2000',
            'images' => 'array|min:0|max:10',
            'images.*' => 'file|max:5120|mimes:png,jpg,jpeg',
        ]);

        $this->stars = $data['stars'] ?? 1;
        $this->reviewer_email = $data['reviewer_email'] ?? "";
        $this->reviewer_name = $data['reviewer_name'] ?? "";
        $this->title = $data['title'] ?? "";
        $this->message = $data['message'] ?? "";

        $images = $data['images'] ?? [];
        foreach ($images as $image) {
            ShopifyReviewImage::store_image($this, $image);
        }

        $this->save();
        $this->triggerUpdationAutomations();
        return $this;
    }

    function validateAndUpdate($input_data): bool
    {
        $data = custom_validate($input_data, [
            self::COLUMN_created_at => 'required|date_format:Y-m-d',
            self::COLUMN_stars => 'required|integer|max:5|min:1',
            self::COLUMN_shopify_product_id => 'required|integer',
            self::COLUMN_reviewer_email => 'required|email|max:100',
            self::COLUMN_reviewer_name => 'required|string|max:100',
            self::COLUMN_title => 'required|string|max:250',
            self::COLUMN_message => 'required|string|max:2000',
            self::COLUMN_verified_purchase => 'required|boolean',
            self::COLUMN_approved => 'required|boolean',
            'images' => 'array|min:0|max:10',
            'images.*' => 'file|max:5120|mimes:png,jpg,jpeg',
        ]);

        $this->created_at = $data['created_at'] ?? $this->created_at;
        $this->stars = $data['stars'] ?? 1;
        $this->shopify_product_id = $data['shopify_product_id'] ?? 1;
        $this->reviewer_email = $data['reviewer_email'] ?? "";
        $this->reviewer_name = $data['reviewer_name'] ?? "";
        $this->title = $data['title'] ?? "";
        $this->message = $data['message'] ?? "";
        $this->verified_purchase = (bool)($data['verified_purchase'] ?? false);
        $this->approved = (bool)($data['approved'] ?? false);

        $images = $data['images'] ?? [];
        foreach ($images as $image) {
            ShopifyReviewImage::store_image($this, $image);
        }

        return $this->save();
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where(self::COLUMN_uid, '=', $uid)->first();
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUidAndKey($uid, $key)
    {
        return self::where(self::COLUMN_uid, $uid)->where(self::COLUMN_secret_key, $key)->firstOrFail();
    }

    public static function findByProductAfterDate($shopify_product_id, $date)
    {
        return self::query()
            ->where(self::COLUMN_shopify_product_id, $shopify_product_id)
            ->where(self::COLUMN_created_at, '>=', $date)
            ->get();
    }

    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order) && in_array($request->sort_order, self::SORTABLE_COLUMNS)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction == "asc" ? "asc" : "desc");
        } else {
            $query->orderBy(self::COLUMN_created_at, 'desc');
        }

        return $query;
    }

    static function filter($request)
    {
        // $user = $request->user();
        $query = self::select('shopify_reviews.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('shopify_reviews.title', 'like', '%' . $keyword . '%');
                    $q->orwhere('shopify_reviews.message', 'like', '%' . $keyword . '%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
        }

        // Other filter
        if (isset($request->customer_id)) {
            $query = $query->where('shopify_reviews.' . self::COLUMN_customer_id, '=', $request->customer_id);
        }
        // Other filter
        if (isset($request->shop_name)) {
            $query = $query->where('shopify_reviews.' . self::COLUMN_shop_name, '=', $request->shop_name);
        }
        // Other filter
        if (isset($request->product_id)) {
            $query = $query->where('shopify_reviews.' . self::COLUMN_shopify_product_id, '=', $request->product_id);
        }
        // Other filter
        if (isset($request->approved)) {
            $query = $query->where('shopify_reviews.' . self::COLUMN_approved, '=', $request->approved);
        }

        return $query;
    }

    function getOrCreateSubscriber()
    {
        if ($this->subscriber)
            return $this->subscriber;

        $names = explode(" ", $this->reviewer_name, 2);
        $subscriber = Subscriber::createSubscriber($this->customer->shopify_shop->mail_list,
            $this->reviewer_email,
            $names[0] ?? "",
            $names[1] ?? ""
        );
        $this->subscriber_id = $subscriber->id;
        $this->save();
        return Subscriber::find($this->subscriber_id);
    }

    function triggerCreationAutomations()
    {
        $customer = $this->customer;
        foreach (Automation2::findByTrigger($customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_SUBMITTED) as $automation2) {
            $context = new ShopifyAutomationContext();
            $context->shopifyReview = $this;
            $context->subscriber = $this->getOrCreateSubscriber();
            dispatch(new RunAutomationWithContext($automation2, $context));
        }
    }

    function triggerUpdationAutomations()
    {
        $customer = $this->customer;
        foreach (Automation2::findByTrigger($customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_UPDATED) as $automation2) {
            $context = new ShopifyAutomationContext();
            $context->shopifyReview = $this;
            $context->subscriber = $this->getOrCreateSubscriber();
            dispatch(new RunAutomationWithContext($automation2, $context));
        }
    }

    function triggerFirstReviewResponseAutomation()
    {
        $count = $this->product->reviews()->count();
        if ($count > 1) return;

        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_REVIEW);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->customer->user->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->shopifyReview = $this;
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    function getReviewGuestEditPageUrl(): string
    {
        return react_route('/review/update/' . $this->uid . '/' . $this->secret_key);
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, self::COLUMN_subscriber_id);
    }

    function product(): BelongsTo
    {
        return $this->belongsTo(ShopifyProduct::class, self::COLUMN_shopify_product_id, ShopifyProduct::COLUMN_shopify_id);
    }

    function images(): HasMany
    {
        return $this->hasMany(ShopifyReviewImage::class, ShopifyReviewImage::COLUMN_review_id);
    }

    function setSecretKey()
    {
        // Create new secret key
        $key = uniqid("review.", true);
        while (self::where(self::COLUMN_secret_key, $key)->count() > 0) {
            $key = uniqid("review.", true);
        }
        $this->secret_key = $key;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
            $item->setSecretKey();
        });

        static::created(function (self $item) {
            $item->getOrCreateSubscriber();
        });
    }
}
