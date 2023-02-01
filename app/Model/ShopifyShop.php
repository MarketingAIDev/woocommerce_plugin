<?php

namespace Acelle\Model;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @property int id
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property string uid
 * @property int user_id
 * @property User user
 * @property int customer_id
 * @property Customer customer
 * @property string name
 * @property string myshopify_domain
 * @property string primary_domain
 * @property string primary_currency
 * @property string access_token
 * @property string scope
 * @property string nonce
 * @property array theme
 * @property boolean active
 * @property boolean initializing
 * @property boolean initialized
 * @property boolean new
 * @property boolean onboarding_complete
 * @property boolean enable_chat_script
 * @property boolean enable_popup_script
 * @property boolean enable_review_script
 * @property string widget_script_tag_id
 * @property boolean default_automations_created
 * @property boolean token_refresh_required
 * @property string storefront_access_token
 *
 * @property MailList mail_list
 * @property ShopifyCheckout[] $shopify_checkouts
 * @property ShopifyCustomer[] shopify_customers
 * @property ShopifyDiscountCode[] shopify_discount_codes
 * @property ShopifyOrder[] shopify_orders
 * @property ShopifyPriceRule[] shopify_price_rules
 * @property ShopifyProduct[] shopify_products
 * @property ShopifyReview[] shopify_reviews
 * @property SyncStatus sync_status
 */
class ShopifyShop extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_uid = 'uid';
    const COLUMN_user_id = 'user_id';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_name = 'name';
    const COLUMN_myshopify_domain = 'myshopify_domain';
    const COLUMN_primary_domain = 'primary_domain';
    const COLUMN_primary_currency = 'primary_currency';
    const COLUMN_access_token = 'access_token';
    const COLUMN_scope = 'scope';
    const COLUMN_nonce = 'nonce';
    const COLUMN_theme = 'theme';
    const COLUMN_active = 'active';
    const COLUMN_initializing = 'initializing';
    const COLUMN_initialized = 'initialized';
    const COLUMN_new = 'new';
    const COLUMN_chat_script_tag_id = 'chat_script_tag_id';
    const COLUMN_popup_script_tag_id = 'popup_script_tag_id';
    const COLUMN_review_script_tag_id = 'review_script_tag_id';
    const COLUMN_onboarding_complete = 'onboarding_complete';
    const COLUMN_enable_chat_script = 'enable_chat_script';
    const COLUMN_enable_popup_script = 'enable_popup_script';
    const COLUMN_enable_review_script = 'enable_review_script';
    const COLUMN_widget_script_tag_id = 'widget_script_tag_id';
    const COLUMN_default_automations_created = 'default_automations_created';
    const COLUMN_token_refresh_required = 'token_refresh_required';
    const COLUMN_storefront_access_token = 'storefront_access_token';

    protected $with = ['sync_status'];

    static function getScopes(): string
    {
        $scopes = [
            "read_orders",
            "read_fulfillments",
            "read_content",
            "read_customers",
            "read_products",
            "read_checkouts",
            "read_themes",
            "write_themes",
            "write_script_tags",
            "write_price_rules",
            "unauthenticated_read_checkouts",
            "unauthenticated_read_customers",
            "unauthenticated_read_customer_tags",
            "unauthenticated_read_content",
            "unauthenticated_read_product_listings",
            "unauthenticated_read_product_tags",
            "unauthenticated_read_selling_plans",
        ];

        if (config('shopify.read_all_orders')) {
            $scopes[] = "read_all_orders";
        }

        return implode(',', $scopes);
    }

    protected $fillable = [
        'customer_id',
        'name',
        'myshopify_domain',
        'primary_domain',
        'access_token',
        'scope',
        'active',
        'nonce',
        'initializing',
        'initialized',
        'theme'
    ];

    protected $hidden = [
        self::COLUMN_access_token,
        self::COLUMN_nonce,
        self::COLUMN_storefront_access_token,
    ];

    protected $casts = [
        self::COLUMN_theme => 'array',
        self::COLUMN_onboarding_complete => 'boolean',
        self::COLUMN_enable_chat_script => 'boolean',
        self::COLUMN_enable_popup_script => 'boolean',
        self::COLUMN_enable_review_script => 'boolean',
        self::COLUMN_token_refresh_required => 'bool',
        self::COLUMN_new => 'bool'
    ];

    protected $appends = [
        'customer_uid'
    ];

    public function getCustomerUidAttribute(): string
    {
        return $this->customer->uid;
    }

    function getStorefrontAccessToken()
    {
        if ($this->storefront_access_token) return $this->storefront_access_token;
        $h = new ShopifyHelper($this);
        $token = $h->createStorefrontAccessToken();
        $this->storefront_access_token = $token;
        $this->save();

        return $token;
    }

    static function triggerWeeklyAutomations()
    {
        $now = Carbon::now();
        $a_week_ago = $now->clone()->subWeek();

        /** @var ShopifyShop[] $active_shops */
        $active_shops = ShopifyShop::query()
            ->where('active', 1)
            ->where('initialized', 1)
            ->where('token_refresh_required', 0)
            ->get();

        self::triggerHighLowSalesAutomations($active_shops, $a_week_ago, $now);
        self::triggerHighMissedChatAutomations($active_shops, $a_week_ago, $now);
        self::triggerFeedbackBounceSpamAutomations($active_shops, $a_week_ago, $now);
    }

    static function triggerHighLowSalesAutomations($shops, $start, $end)
    {
        /** @var ShopifyShop[] $shops */
        foreach ($shops as $shop) {
            $sales_count = $shop->shopify_orders()
                ->where('shopify_created_at', '>=', $start)
                ->where('shopify_created_at', '<=', $end)
                ->count();

            if ($sales_count >= 10) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_SALES);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }

            if ($sales_count <= 2) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_LOW_SALES);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }
        }
    }

    static function triggerHighMissedChatAutomations($shops, $start, $end)
    {
        /** @var ShopifyShop[] $shops */
        foreach ($shops as $shop) {
            $missed_chats_count = $shop->customer->chat_sessions()
                ->where(ChatSession::COLUMN_agent_unread_messages, '>=', 1)
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count();

            if ($missed_chats_count >= 5) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_MISSED_CHATS);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }
        }
    }

    static function triggerFeedbackBounceSpamAutomations($shops, $start, $end)
    {
        /** @var ShopifyShop[] $shops */
        foreach ($shops as $shop) {
            $delivered_count = $shop->customer->trackingLogs()
                ->where('status', '!=', TrackingLog::STATUS_FAILED)
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count() ?: 1;
            $unsubscribe_count = $shop->customer->unsubscribeLogs()
                ->where('tracking_logs.created_at', '>=', $start)
                ->where('tracking_logs.created_at', '<=', $end)
                ->count();
            $spam_count = $shop->customer->feedbackLogs()
                ->where('tracking_logs.created_at', '>=', $start)
                ->where('tracking_logs.created_at', '<=', $end)
                ->count();
            $bounce_count = $shop->customer->bounceLogs()
                ->where('tracking_logs.created_at', '>=', $start)
                ->where('tracking_logs.created_at', '<=', $end)
                ->count();
            $five_percent = $delivered_count * 5.0 / 100.0;

            if ($unsubscribe_count > $five_percent) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_UNSUBSCRIBE_RATE);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }

            if ($spam_count > $five_percent) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_SPAM_RATE);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }

            if ($bounce_count > $five_percent) {
                $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_HIGH_SPAM_RATE);
                foreach ($automation2s as $automation2) {
                    $subscriber = $automation2->mailList->subscribers()->where('email', $shop->customer->user->email)->first();
                    if ($subscriber) {
                        $context = new ShopifyAutomationContext();
                        $context->shopifyShop = $shop;
                        $context->subscriber = $subscriber;
                        dispatch(new RunAutomationWithContext($automation2, $context));
                    }
                }
            }
        }
    }

    static function createMailLists()
    {
        $shops = self::query()->whereDoesntHave('mail_list')->cursor();
        /** @var ShopifyShop $shopifyShop */
        foreach ($shops as $shopifyShop) {
            $contact = $shopifyShop->customer->contact;

            $list = new MailList();
            $list->all_sending_servers = true;
            $list->name = 'Shopify Customers: ' . $shopifyShop->name;
            $list->from_email = $contact->email;
            $list->from_name = $contact->first_name;
            $list->email_subscribe = $contact->email;;
            $list->email_unsubscribe = $contact->email;;
            $list->email_daily = $contact->email;;
            $list->customer_id = $shopifyShop->customer_id;
            $list->contact_id = $contact->id;
            $shopifyShop->mail_list()->save($list);

            $shopifyShop->initialized = 0;
            $shopifyShop->initializing = 0;
            $shopifyShop->save();

            if ($shopifyShop->user->isAdmin()) {
                $ew_users = User::query()->cursor();
                /** @var User $ew_user */
                foreach ($ew_users as $ew_user) {
                    $ew_user->createAdminSubscribers();
                }
            }
        }
    }

    // region Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COLUMN_user_id);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, self::COLUMN_customer_id);
    }

    public function mail_list(): HasOne
    {
        return $this->hasOne(MailList::class, MailList::COLUMN_shopify_shop_id);
    }

    public function shopify_checkouts(): HasMany
    {
        return $this->hasMany(ShopifyCheckout::class, ShopifyCheckout::COLUMN_shop_id);
    }

    public function shopify_customers(): HasMany
    {
        return $this->hasMany(ShopifyCustomer::class, ShopifyCustomer::COLUMN_shop_id);
    }

    public function shopify_discount_codes(): HasMany
    {
        return $this->hasMany(ShopifyDiscountCode::class, ShopifyDiscountCode::COLUMN_shop_id);
    }

    public function shopify_orders(): HasMany
    {
        return $this->hasMany(ShopifyOrder::class, ShopifyOrder::COLUMN_shop_id);
    }

    public function shopify_price_rules(): HasMany
    {
        return $this->hasMany(ShopifyPriceRule::class, ShopifyPriceRule::COLUMN_shop_id);
    }

    public function shopify_products(): HasMany
    {
        return $this->hasMany(ShopifyProduct::class, ShopifyProduct::COLUMN_shop_id);
    }

    public function shopify_reviews(): HasMany
    {
        return $this->hasMany(ShopifyReview::class, ShopifyReview::COLUMN_shop_id);
    }

    public function shopify_recurring_application_charges(): HasMany
    {
        return $this->hasMany(ShopifyReview::class, ShopifyRecurringApplicationCharge::COLUMN_shop_id);
    }

    public function shopify_blogs(): HasMany
    {
        return $this->hasMany(ShopifyBlog::class, ShopifyBlog::COLUMN_shop_id);
    }

    public function shopify_pages(): HasMany
    {
        return $this->hasMany(ShopifyPage::class, ShopifyPage::COLUMN_shop_id);
    }

    public function sync_status(): HasOne
    {
        return $this->hasOne(SyncStatus::class, SyncStatus::COLUMN_shopify_shop_id);
    }

    // endregion

    public static function storeRules(): array
    {
        return [
            'myshopify_domain' => [
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/',
                'unique:shopify_shops',
                'required'
            ]
        ];
    }

    public static function subscribeRules(): array
    {
        return [
            'shopify_shop' => 'required|exists:shopify_shops,shop',
            'plan_id' => 'required|exists:plans,id'
        ];
    }

    public static function rules(): array
    {
        return [
            'myshopify_domain' => 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/'
        ];
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

    /**
     * Find item by shop name and customer.
     *
     * @param $shop
     * @return ShopifyShop
     */
    public static function findByMyShopifyDomain($shop)
    {
        return self::where('myshopify_domain', '=', $shop)->first();
    }

    public static function findByMyShopifyDomainOrFail($shop)
    {
        $model = self::findByMyShopifyDomain($shop);
        if (!$model) {
            throw new NotFoundHttpException('The shopify shop doesn\'t exist in our records');
        }
        return $model;
    }

    public static function findByUidOrFail($shop)
    {
        $model = self::findByUid($shop);
        if (!$model) {
            throw new NotFoundHttpException('The shopify shop doesn\'t exist');
        }
        return $model;
    }

    public static function paged_search($request)
    {
        return self::where('user_id', $request->user()->id)->paginate($request->per_page ?? 20);
    }

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function (self $item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
            $item->scope = self::getScopes();
        });

        static::deleting(function (self $item) {
            if ($item->mail_list)
                $item->mail_list->delete();
            foreach ($item->shopify_checkouts as $sub_item)
                $sub_item->delete();
            foreach ($item->shopify_customers as $sub_item)
                $sub_item->delete();
            foreach ($item->shopify_orders as $sub_item)
                $sub_item->delete();
            foreach ($item->shopify_products as $sub_item)
                $sub_item->delete();
            $item->customer->delete();
            if ($item->user->customers()->count() == 0)
                $item->user->delete();
        });
    }
}