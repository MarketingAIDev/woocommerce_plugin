<?php

/**
 * Customer class.
 *
 * Model class for customer
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Library\QuotaTrackerFile;
use Acelle\Library\Tool;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

/**
 * Class Customer
 * @package Acelle\Model
 *
 * @property integer id
 * @property string uid
 * @property integer user_id
 * @property integer admin_id
 * @property integer contact_id
 * @property integer language_id
 * @property string first_name
 * @property string last_name
 * @property string image
 * @property string timezone
 * @property string status
 * @property string color_scheme
 * @property array quota
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property string cache
 * @property string text_direction
 * @property string last_activity
 * @property array chat_settings
 * @property array review_settings
 * @property string|Carbon automations_imported_at
 * @property string|Carbon popups_imported_at
 *
 * @property User user
 * @property Admin admin
 * @property Contact contact
 *
 * @property ShopifyShop shopify_shop
 * @property Automation2[] activeAutomation2s
 * @property SendingDomain[] activeDkimSendingDomains
 * @property EmailVerificationServer[] activeEmailVerificationServers
 * @property SendingDomain[] activeSendingDomains
 * @property SendingDomain[] activeSubscription
 * @property SendingDomain[] activeVerifiedSendingDomains
 * @property Sender[] allSenders
 * @property Automation2[] automation2s
 * @property Blacklist[] blacklists
 * @property Campaign[] campaigns
 * @property ChatSession[] chat_sessions
 * @property EmailVerificationServer[] emailVerificationServers
 * @property GalleryImage[] gallery
 * @property Language language
 * @property MailList[] lists
 * @property Log[] logs
 * @property Popup[] popups
 * @property Popup[] active_popups
 * @property PopupResponse[] popup_responses
 * @property ShopifyRecurringApplicationCharge[] shopifyRecurringApplicationCharges
 * @property Segment2[] segment2s
 * @property Sender[] senders
 * @property SendingDomain[] sendingDomains
 * @property Campaign[] sentCampaigns
 * @property Signature signature
 * @property SocialMediaLink[] social_media_links
 * @property Subscriber[] subscribers
 * @property TrackingDomain[] trackingDomains
 * @property TrackingLog[] trackingLogs
 * @property SalesChannels salesChannels
 * @property ShopifyReview[] shopifyReviews
 */
class Customer extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_user_id = 'user_id';
    const COLUMN_admin_id = 'admin_id';
    const COLUMN_contact_id = 'contact_id';
    const COLUMN_language_id = 'language_id';
    const COLUMN_first_name = 'first_name';
    const COLUMN_last_name = 'last_name';
    const COLUMN_image = 'image';
    const COLUMN_timezone = 'timezone';
    const COLUMN_status = 'status';
    const COLUMN_color_scheme = 'color_scheme';
    const COLUMN_quota = 'quota';
    const COLUMN_cache = 'cache';
    const COLUMN_text_direction = 'text_direction';
    const COLUMN_last_activity = 'last_activity';
    const COLUMN_chat_settings = 'chat_settings';
    const COLUMN_review_settings = 'review_settings';
    const COLUMN_automations_imported_at = 'automations_imported_at';
    const COLUMN_popups_imported_at = 'popups_imported_at';

    protected $quotaTracker;

    // Plan status
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'first_name', 'last_name', 'timezone', 'language_id', 'color_scheme', 'text_direction', 'chat_settings', 'review_settings'
    ];

    protected $casts = [
        self::COLUMN_chat_settings => 'array',
        self::COLUMN_review_settings => 'array',
    ];

    protected $dates = [
        self::COLUMN_popups_imported_at,
        self::COLUMN_automations_imported_at,
    ];

    protected $appends = [
        'plan'
    ];

    function getPlanAttribute(): ?Plan
    {
        $billing = $this->getActiveShopifyRecurringApplicationCharge();
        return $billing ? $billing->plan : null;
    }

    const DEFAULT_CHAT_SETTINGS = [
        "info_message" => "Live Chat",
        "welcome_message" => "Please tell us more about you.",
        "bot_name" => "Emailwish Bot",
        "bot_image" => "/storage/bot_images/default.png",

        "primary_background_color" => "#000000ff",
        "primary_text_color" => "#ffffffff",
        "secondary_background_color" => "#ffffffff",
        "secondary_text_color" => "#a1a1a1ff",
        "font_family" => "",

        "guest_message_background_color" => "#b18787",
        "guest_message_text_color" => "#ffffff",
        "admin_message_background_color" => "#ebebeb",
        "admin_message_text_color" => "#000000",

        "input_background_color" => "#ffffff",
        "input_text_color" => "#000000",

        "offline_message" => "Sorry we're currently unavailable.",
        "file_sharing_enabled" => true,
        "file_sharing_max_size_kb" => ChatMessage::ATTACHMENT_MAX_SIZE_KB,
        "file_sharing_extensions" => ChatMessage::ATTACHMENT_ACCEPTED_EXTENSIONS,
    ];

    const DEFAULT_REVIEW_SETTINGS = [
        "primary_background_color" => "#ffffffff",
        "primary_text_color" => "#000000ff",
        "secondary_background_color" => "#000000ff",
        "secondary_text_color" => "#ffffffff",
        "active_star_color" => "#000000ff",
        "inactive_star_color" => "#e0e0e7ff",
        "font_family" => "",
        "secondary_font_family" => "",
        "separator_color" => "#e7e7e7ff"
    ];

    function getReviewSettings()
    {
        $settings = [];
        foreach (self::DEFAULT_REVIEW_SETTINGS as $key => $default_value) {
            $settings[$key] = $this->review_settings[$key] ?? $default_value;
        }
        return $settings;
    }

    function getChatSettings()
    {
        $settings = [];
        foreach (self::DEFAULT_CHAT_SETTINGS as $key => $default_value) {
            if ($key == "info_message" && $this->shopify_shop)
                $default_value = "Welcome to " . $this->shopify_shop->name . " Support";
            $settings[$key] = $this->chat_settings[$key] ?? $default_value;
        }
        return $settings;
    }

    function setChatSettings()
    {
        $settings = self::DEFAULT_CHAT_SETTINGS;
        if ($this->shopify_shop)
            $settings["info_message"] = "Welcome to " . $this->shopify_shop->name . " Support";
        $settings['bot_image'] = '/assets/images/faces/' . rand(1, 54) . '.jpg';
        $this->chat_settings = $settings;
        $this->save();
    }

    function updateChatColors($data)
    {
        $settings = $this->getChatSettings();
        $settings["primary_background_color"] = $data["primary_background_color"] ?? "";
        $settings["primary_text_color"] = $data["primary_text_color"] ?? "";
        $settings["secondary_background_color"] = $data["secondary_background_color"] ?? "";
        $settings["secondary_text_color"] = $data["secondary_text_color"] ?? "";
        $settings["font_family"] = $data["font_family"] ?? "";
        $this->chat_settings = $settings;
        $this->save();
    }

    function updateReviewColors($data)
    {
        $settings = $this->getReviewSettings();
        $settings["primary_background_color"] = $data["primary_background_color"] ?? "";
        $settings["primary_text_color"] = $data["primary_text_color"] ?? "";
        $settings["secondary_background_color"] = $data["secondary_background_color"] ?? "";
        $settings["secondary_text_color"] = $data["secondary_text_color"] ?? "";
        $settings["font_family"] = $data["font_family"] ?? "";
        $this->review_settings = $settings;
        $this->save();
    }

    function validateAndUpdateChatSettings($input_data)
    {
        $data = custom_validate($input_data, [
            "info_message" => "required|string|max:255",
            "welcome_message" => "required|string|max:255",
            "bot_name" => "required|string|max:255",
            "bot_image_file" => "nullable|file|max:1024|mimes:png,jpg,jpeg",

            "primary_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "primary_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "secondary_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "secondary_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "font_family" => "nullable|string|max:255",

            "guest_message_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "guest_message_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "admin_message_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "admin_message_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",

            "input_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "input_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",

            "offline_message" => "required|string|max:500",
            "file_sharing_enabled" => "required|boolean"
        ]);

        $settings = $this->getChatSettings();
        foreach (Customer::DEFAULT_CHAT_SETTINGS as $default_key => $default_value) {
            if (!key_exists($default_key, $settings))
                $settings[$default_key] = $default_value;
        }

        $settings["info_message"] = $data["info_message"] ?? "";
        $settings["welcome_message"] = $data["welcome_message"] ?? "";
        $settings["bot_name"] = $data["bot_name"] ?? "";

        $settings["primary_background_color"] = $data["primary_background_color"] ?? "";
        $settings["primary_text_color"] = $data["primary_text_color"] ?? "";
        $settings["secondary_background_color"] = $data["secondary_background_color"] ?? "";
        $settings["secondary_text_color"] = $data["secondary_text_color"] ?? "";
        $settings["font_family"] = $data["font_family"] ?? "";

        $settings["guest_message_background_color"] = $data["guest_message_background_color"] ?? "";
        $settings["guest_message_text_color"] = $data["guest_message_text_color"] ?? "";
        $settings["admin_message_background_color"] = $data["admin_message_background_color"] ?? "";
        $settings["admin_message_text_color"] = $data["admin_message_text_color"] ?? "";

        $settings["input_background_color"] = $data["input_background_color"] ?? "";
        $settings["input_text_color"] = $data["input_text_color"] ?? "";

        $settings["offline_message"] = $data["offline_message"] ?? "";
        $settings["file_sharing_enabled"] = (bool)$data["file_sharing_enabled"] ?? "";

        $bot_image = $data['bot_image_file'] ?? null;
        if ($bot_image) {
            $settings["bot_image"] = $this->storeChatbotImage($bot_image);
        }
        $this->chat_settings = $settings;
        $this->save();

    }

    function validateAndUpdateReviewSettings($input_data)
    {
        $data = custom_validate($input_data, [
            "primary_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "primary_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "secondary_text_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "secondary_background_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "active_star_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "inactive_star_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
            "font_family" => "nullable|string|max:255",
            "secondary_font_family" => "nullable|string|max:255",
            "separator_color" => "required|regex:/^#[0-9a-fA-F]{3,8}$/",
        ]);

        $settings = $this->getReviewSettings();
        foreach (Customer::DEFAULT_REVIEW_SETTINGS as $default_key => $default_value) {
            if (!key_exists($default_key, $settings))
                $settings[$default_key] = $default_value;
        }

        $settings["primary_background_color"] = $data["primary_background_color"] ?? "";
        $settings["primary_text_color"] = $data["primary_text_color"] ?? "";
        $settings["secondary_background_color"] = $data["secondary_background_color"] ?? "";
        $settings["secondary_text_color"] = $data["secondary_text_color"] ?? "";
        $settings["active_star_color"] = $data["active_star_color"] ?? "";
        $settings["inactive_star_color"] = $data["inactive_star_color"] ?? "";
        $settings["font_family"] = $data["font_family"] ?? "";
        $settings["secondary_font_family"] = $data["secondary_font_family"] ?? "";
        $settings["separator_color"] = $data["separator_color"] ?? "";

        $this->review_settings = $settings;
        $this->save();

    }

    public function rules()
    {
        $rules = array(
            'email' => 'required|email|unique:users,email,' . $this->user_id . ',id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'timezone' => 'required',
            'language_id' => 'required',
        );

        if (isset($this->id)) {
            $rules['password'] = 'confirmed|min:5';
        } else {
            $rules['password'] = 'required|confirmed|min:5';
        }

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function registerRules()
    {
        $rules = array(
            'email' => 'required|email|unique:users,email,' . $this->user_id . ',id',
            'first_name' => 'required',
            'last_name' => 'required',
            'timezone' => 'required',
            'language_id' => 'required',
        );

        if (isset($this->id)) {
            $rules['password'] = 'min:5';
        } else {
            $rules['password'] = 'required|min:5';
        }

        return $rules;
    }

    /**
     * Customer email.
     *
     * @return string
     */
    public function email()
    {
        return is_object($this->user) ? $this->user->email : '';
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->firstOrFail();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function chat_sessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(MailList::class)->orderBy('created_at', 'desc');
    }

    public function segment2s(): HasMany
    {
        return $this->hasMany(Segment2::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class)->orderBy('created_at', 'desc');
    }

    public function sentCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class)->where('status', '=', 'done')->orderBy('created_at', 'desc');
    }

    public function subscribers(): HasManyThrough
    {
        return $this->hasManyThrough(Subscriber::class, MailList::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class)->orderBy('created_at', 'desc');
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class)->orderBy('created_at', 'asc');
    }

    public function clickLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            ClickLog::class,
            TrackingLog::class,
            TrackingLog::COLUMN_customer_id,
            'message_id',
            'id',
            'message_id');
    }

    public function openLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            OpenLog::class,
            TrackingLog::class,
            TrackingLog::COLUMN_customer_id,
            'message_id',
            'id',
            'message_id');
    }

    public function bounceLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            BounceLog::class,
            TrackingLog::class,
            TrackingLog::COLUMN_customer_id,
            'message_id',
            'id',
            'message_id');
    }

    public function feedbackLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            FeedbackLog::class,
            TrackingLog::class,
            TrackingLog::COLUMN_customer_id,
            'message_id',
            'id',
            'message_id');
    }

    public function unsubscribeLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            UnsubscribeLog::class,
            TrackingLog::class,
            TrackingLog::COLUMN_customer_id,
            'message_id',
            'id',
            'message_id');
    }

    public function automation2s(): HasMany
    {
        return $this->hasMany(Automation2::class);
    }

    public function activeAutomation2s(): HasMany
    {
        return $this->hasMany(Automation2::class)->where('status', Automation2::STATUS_ACTIVE);
    }

    public function sendingDomains(): HasMany
    {
        return $this->hasMany(SendingDomain::class);
    }

    public function popups(): HasMany
    {
        return $this->hasMany(Popup::class, Popup::COLUMN_customer_id);
    }

    public function active_popups(): HasMany
    {
        return $this->hasMany(Popup::class, Popup::COLUMN_customer_id)
            ->where(function ($query) {
                $query->where(Popup::COLUMN_active, '!=', 0)
                    ->orWhere(Popup::COLUMN_active_mobile, '!=', 0);
            });
    }

    public function popup_responses(): HasMany
    {
        return $this->hasMany(PopupResponse::class, PopupResponse::COLUMN_customer_id);
        //return $this->hasManyThrough(PopupResponse::class, Popup::class, Popup::COLUMN_customer_id, PopupResponse::COLUMN_popup_id);
    }

    public function activeSendingDomains(): HasMany
    {
        return $this->sendingDomains()->where('status', '=', SendingDomain::STATUS_ACTIVE);
    }

    public function activeDkimSendingDomains(): HasMany
    {
        return $this->activeSendingDomains()->where('dkim_verified', true);
    }

    public function emailVerificationServers(): HasMany
    {
        return $this->hasMany(EmailVerificationServer::class);
    }

    public function activeEmailVerificationServers(): HasMany
    {
        return $this->emailVerificationServers()->where('status', '=', EmailVerificationServer::STATUS_ACTIVE);
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(Blacklist::class);
    }

    // Only direct senders children
    public function senders(): HasMany
    {
        return $this->hasMany(Sender::class);
    }

    // tracking domain
    public function trackingDomains(): HasMany
    {
        return $this->hasMany(TrackingDomain::class);
    }

    // All senders, including ones that are not associated to a particular sending servers
    public function allSenders(): HasMany
    {
        $plan = $this->getActiveShopifyRecurringApplicationCharge()->plan;
        if ($plan->useSystemSendingServer()) {
            $server = $plan->primarySendingServer();

            if (is_null($server)) {
                throw new Exception('No sending server available for plan #Customer::allSenders');
            }

            $result = $this->senders()->whereRaw(sprintf("%s IS NULL OR %s = %s", table('senders.sending_server_id'), table('senders.sending_server_id'), $server->id));
        } else {
            $result = $this->senders();
        }

        return $result;
    }

    public function activeVerifiedSendingDomains(): HasMany
    {
        $plan = $this->getActiveShopifyRecurringApplicationCharge()->plan;

        if ($plan->useSystemSendingServer()) {
            $server = $plan->primarySendingServer();

            if (is_null($server)) {
                throw new Exception('No sending server available for plan #Customer::activeVerifiedSendingDomains');
            }

            return $this->sendingDomains()
                ->where('status', '=', SendingDomain::STATUS_ACTIVE)
                ->where('domain_verified', '=', SendingDomain::VERIFIED)
                ->whereRaw(sprintf('(%s IS NULL or %s = %s)', table('sending_domains.sending_server_id'), table('sending_domains.sending_server_id'), $server->id));
        } else {
            return $this->sendingDomains()
                ->where('status', '=', SendingDomain::STATUS_ACTIVE)
                ->where('domain_verified', '=', SendingDomain::VERIFIED);
        }
    }

    public function social_media_links(): HasMany
    {
        return $this->hasMany(SocialMediaLink::class);
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }

    public function signature(): HasOne
    {
        return $this->hasOne(Signature::class, Signature::COLUMN_customer_id);
    }

    //todo: remove this
    public function activeSubscription()
    {
        return (is_object($this->subscription) && $this->subscription->isActive()) ? $this->subscription : null;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function (self $item) {
            // Create new uid
            $uid = uniqid();
            while (Customer::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });

        static::created(function (self $item) {
            SalesChannels::createSalesChannel($item);
        });
    }

    /**
     * Display customer name: first_name last_name.
     *
     * @var string
     */
    public function displayName()
    {
        return htmlspecialchars(trim($this->first_name . ' ' . $this->last_name));
    }

    /**
     * Upload and resize avatar.
     *
     * @var void
     */
    public function uploadImage($file)
    {
        $path = 'app/customers/';
        $upload_path = storage_path($path);

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = 'avatar-' . $this->id . '.' . $file->getClientOriginalExtension();

        // save to server
        $file->move($upload_path, $filename);

        // create thumbnails
        $img = Image::make($upload_path . $filename);
        $img->fit(120, 120)->save($upload_path . $filename . '.thumb.jpg');

        return $path . $filename;
    }

    public function storeChatbotImage($file)
    {
        $path = $file->store('bot_images', 'public');
        $full_path = storage_path('app/public/' . $path);

        $img = Image::make($full_path);
        $img->fit(120, 120)->save($full_path);
        return '/storage/' . $path;
    }

    public function imagePath()
    {
        if (!empty($this->image) && !empty($this->id)) {
            return storage_path($this->image) . '.thumb.jpg';
        } else {
            return '';
        }
    }

    /**
     * Get image thumb path.
     *
     * @var string
     */
    public function removeImage()
    {
        if (!empty($this->image) && !empty($this->id)) {
            $path = storage_path($this->image);
            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($path . '.thumb.jpg')) {
                unlink($path . '.thumb.jpg');
            }
        }
    }

    public static function getAll()
    {
        return self::select('customers.*');
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    public static function filter($request)
    {
        $query = self::select('customers.*')
            ->leftJoin('users', 'users.id', '=', 'customers.user_id');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('customers.first_name', 'like', '%' . $keyword . '%')
                        ->orWhere('customers.last_name', 'like', '%' . $keyword . '%')
                        ->orWhere('users.email', 'like', '%' . $keyword . '%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
        }

        // Admin filter
        if (!empty($request->admin_id)) {
            $query = $query->where('customers.admin_id', '=', $request->admin_id);
        }
        // User filter
        if (!empty($request->user_id)) {
            $query = $query->where('customers.user_id', '=', $request->user_id);
            //dd(str_replace_array('?', $query->getBindings(), $query->toSql()));
        }

        return $query;
    }

    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    public static function paged_search($request)
    {
        return self::where('user_id', $request->user()->id)->paginate($request->per_page ?? 20);
    }

    /**
     * Subscribers count by time.
     *
     * @return number
     */
    public static function subscribersCountByTime($begin, $end, $customer_id = null, $list_id = null)
    {
        $query = Subscriber::leftJoin('mail_lists', 'mail_lists.id', '=', 'subscribers.mail_list_id')
            ->leftJoin('customers', 'customers.id', '=', 'mail_lists.customer_id');

        if (isset($list_id)) {
            $query = $query->where('subscribers.mail_list_id', '=', $list_id);
        }
        if (isset($customer_id)) {
            $query = $query->where('customers.id', '=', $customer_id);
        }

        $query = $query->where('subscribers.created_at', '>=', $begin)
            ->where('subscribers.created_at', '<=', $end);

        return $query->count();
    }

    public function maxLists()
    {
        $count = $this->getOption('list_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Count customer lists.
     *
     * @return number
     */
    public function listsCount()
    {
        return $this->lists()->count();
    }

    /**
     * Calculate list usage.
     *
     * @return number
     */
    public function listsUsage()
    {
        $max = $this->maxLists();
        $count = $this->listsCount();

        if ($max == '∞') {
            return 0;
        }

        if ($max == 0) {
            return 0;
        }

        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    public function displayListsUsage()
    {
        if ($this->maxLists() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->listsUsage() . '%';
    }

    public function maxCampaigns()
    {
        $count = $this->getOption('campaign_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Count customer's campaigns.
     *
     * @return number
     */
    public function campaignsCount()
    {
        return $this->campaigns()->count();
    }

    /**
     * Calculate campaign usage.
     *
     * @return number
     */
    public function campaignsUsage()
    {
        $max = $this->maxCampaigns();
        $count = $this->campaignsCount();

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    public function displayCampaignsUsage()
    {
        if ($this->maxCampaigns() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->campaignsUsage() . '%';
    }

    public function maxSubscribers()
    {
        $count = $this->getOption('subscriber_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Count customer's subscribers.
     *
     * @return number
     */
    public function subscribersCount($cache = false)
    {
        if ($cache) {
            return $this->readCache('SubscriberCount');
        }

        // return distinctCount($this->subscribers(), 'subscribers.email', 'distinct');
        return $this->subscribers()->count();
    }

    /**
     * Calculate subscibers usage.
     *
     * @return number
     */
    public function subscribersUsage($cache = false)
    {
        $max = $this->maxSubscribers();
        $count = $this->subscribersCount($cache);

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    public function displaySubscribersUsage()
    {
        if ($this->maxSubscribers() == '∞') {
            return trans('messages.unlimited');
        }

        // @todo: avoid using cached value in a function
        //        cache value must be called directly from view only
        return $this->readCache('SubscriberUsage', 0) . '%';
    }

    /**
     * Get customer's quota.
     *
     * @return string
     */
    public function maxQuota()
    {
        $quota = $this->getOption('sending_quota');
        if ($quota == '-1') {
            return '∞';
        } else {
            return $quota;
        }
    }

    /**
     * Check if customer has access to ALL sending servers.
     *
     * @return bool
     */
    public function allSendingServer()
    {
        $check = $this->getOption('all_sending_servers');

        return $check == 'yes';
    }

    /**
     * Check if customer has used up all quota allocated.
     *
     */
    public function overQuota()
    {
        return !$this->getQuotaTracker()->check();
    }

    public function debugQuota()
    {
        return $this->getQuotaTracker()->debugCheck();
    }

    /**
     * Increment quota usage.
     */
    public function countUsage($msgId, Carbon $timePoint = null)
    {
        $charge = $this->getActiveShopifyRecurringApplicationCharge();
        if ($charge) {
            ShopifyUsageChargeEntry::storeEntry($charge, $msgId);
        }
        return $this->getQuotaTracker()->add($timePoint);
    }

    /**
     * Get customer's sending quota rate.
     *
     * @return string
     */
    public function displaySendingQuotaUsage()
    {
        if ($this->getSendingQuota() == -1) {
            return trans('messages.unlimited');
        }
        // @todo use percentage helper here
        return $this->getSendingQuotaUsagePercentage() . '%';
    }

    /**
     * Clean up the quota tracking files to prevent it from growing too large.
     */
    public function cleanupQuotaTracker()
    {
        $this->getQuotaTracker()->cleanupSeries();
    }

    /**
     * Get customer's color scheme.
     *
     * @return string
     */
    public function getColorScheme()
    {
        if (!empty($this->color_scheme)) {
            return $this->color_scheme;
        } else {
            return Setting::get('frontend_scheme');
        }
    }

    /**
     * Color array.
     *
     * @return array
     */
    public static function colors($default)
    {
        return [
            ['value' => '', 'text' => trans('messages.system_default')],
            ['value' => 'blue', 'text' => trans('messages.blue')],
            ['value' => 'green', 'text' => trans('messages.green')],
            ['value' => 'brown', 'text' => trans('messages.brown')],
            ['value' => 'pink', 'text' => trans('messages.pink')],
            ['value' => 'grey', 'text' => trans('messages.grey')],
            ['value' => 'white', 'text' => trans('messages.white')],
        ];
    }

    /**
     * Disable customer.
     *
     * @return bool
     */
    public function disable()
    {
        $this->status = 'inactive';

        return $this->save();
    }

    /**
     * Enable customer.
     *
     * @return bool
     */
    public function enable()
    {
        $this->status = 'active';

        return $this->save();
    }

    /**
     * Get customer's quota.
     *
     * @return string
     */
    public function getSendingQuota()
    {
        return $this->getMaxPaidEmails() + $this->getMaxFreeEmails();
    }

    function getMaxPaidEmails(): int
    {
        return (int)$this->getOption('email_max');
    }

    function getMaxFreeEmails(): int
    {
        return (int)$this->getOption('email_max_free');
    }

    /**
     * Get customer's sending quota.
     *
     * @return string
     */
    public function getSendingQuotaUsage()
    {
        $tracker = $this->getQuotaTracker();

        return $tracker->getUsage();
    }

    /**
     * Get customer's sending quota rate.
     *
     * @return string
     */
    public function getSendingQuotaUsagePercentage()
    {
        $max = $this->getSendingQuota();
        $count = $this->getSendingQuotaUsage();

        if ($max == -1) {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    /**
     * Get the quota/limit object.
     *
     * @return array
     */
    public function getQuotaHash()
    {
        if(!$this->shopify_shop->active)
            return [
                'start' => $this->created_at->timestamp,
                'max' => 0
            ];
        $current = $this->getActiveShopifyRecurringApplicationCharge();

        if (is_null($current)) {
            throw new Exception('Application error: subscription started_at not set!');
        }

        return [
            'start' => $current->created_at->timestamp,
            'max' => $current->isInTrial() ? 50 : ($this->getOption('email_max') + $this->getOption('email_max_free')),
        ];
    }

    /**
     * Initialize the quota tracker.
     *
     */
    public function initQuotaTracker()
    {
        $this->quotaTracker = new QuotaTrackerFile($this->getSendingQuotaLockFile(), $this->getQuotaHash(), $this->getSendingLimits());
        $this->quotaTracker->cleanupSeries();
        // @note: in case of multi-process, the following command must be issued manually
        //     $this->renewQuotaTracker();
    }

    public function getSendingLimits()
    {
        $timeValue = $this->getOption('sending_quota_time');
        if ($timeValue == -1) {
            return []; // no limit
        }
        $timeUnit = $this->getOption('sending_quota_time_unit');
        $limit = $this->getOption('sending_quota');

        return ["{$timeValue} {$timeUnit}" => $limit];
    }

    /**
     * Get sending quota lock file.
     *
     * @return string file path
     */
    public function getSendingQuotaLockFile()
    {
        return storage_path("app/customer/quota/{$this->uid}");
    }

    /**
     * Get customer's QuotaTracker.
     *
     * @return QuotaTrackerFile
     */
    public function getQuotaTracker()
    {
        if (!$this->quotaTracker) {
            $this->initQuotaTracker();
        }

        return $this->quotaTracker;
    }

    /**
     * Get not auto campaign.
     *
     * @return array
     */
    public function getNormalCampaigns()
    {
        return $this->campaigns()->where('is_auto', '=', false);
    }

    /**
     * Get customer timezone.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get customer language code.
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return is_object($this->language) ? $this->language->code : null;
    }

    /**
     * Get customer language code.
     *
     * @return string
     */
    public function getLanguageCodeFull()
    {
        if (!is_object($this->language))
            return null;
        $region_code = $this->language->region_code ? strtoupper($this->language->region_code) : strtoupper($this->language->code);
        return is_object($this->language) ? ($this->language->code . '-' . $region_code) : null;
    }

    /**
     * Get customer select2 select options.
     *
     */
    public static function select2($request)
    {
        $data = ['items' => [], 'more' => true];

        $query = Customer::getAll()->leftJoin('users', 'users.id', '=', 'customers.user_id');
        if (isset($request->q)) {
            $keyword = $request->q;
            $query = $query->where(function ($q) use ($keyword) {
                $q->orwhere('customers.first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.email', 'like', '%' . $keyword . '%');
            });
        }

        // Read all check
        if (!$request->user()->admin->can('readAll', new Customer())) {
            $query = $query->where('customers.admin_id', '=', $request->user()->admin->id);
        }

        foreach ($query->limit(20)->get() as $customer) {
            $data['items'][] = ['id' => $customer->uid, 'text' => $customer->displayNameEmailOption()];
        }

        return json_encode($data);
    }

    /**
     * Create/Update customer information.
     *
     */
    public function updateNewCustomer(Request $request)
    {
        // Create user account for customer
        $user = is_object($this->user) ? $this->user : new User();
        $user->email = $request->email;
        // Update password
        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
        }

        $af_id = $request->session()->get('af_id');
        if (empty($user->affiliation_id) && $af_id) {
            $user->affiliation_id = $af_id;
        }
        $user->save();

        // Save current user info
        if (!$this->id) {
            $this->user_id = $user->id;
            $this->admin_id = is_object($request->user()) ? $request->user()->admin->id : null;
            $this->status = 'active';
        }

        $this->fill($request->all());

        $this->save();

        // Upload and save image
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                // Remove old images
                $this->removeImage();
                $this->image = $this->uploadImage($request->file('image'));
                $this->save();
            }
        }

        // Remove image
        if ($request->_remove_image == 'true') {
            $this->removeImage();
            $this->image = '';
        }
    }

    public function sendingServers()
    {
        return $this->hasMany(SendingServer::class);
    }

    public function subAccounts()
    {
        return $this->hasMany(SubAccount::class);
    }

    /**
     * Customers count by time.
     *
     * @return number
     */
    public static function customersCountByTime($begin, $end, $admin = null)
    {
        $query = Customer::select('customers.*');

        if (isset($admin) && !$admin->can('readAll', new Customer())) {
            $query = $query->where('customers.admin_id', '=', $admin->id);
        }

        $query = $query->where('customers.created_at', '>=', $begin)
            ->where('customers.created_at', '<=', $end);

        return $query->count();
    }

    /**
     * The rules for validation via api.
     *
     * @var array
     */
    public function apiRules()
    {
        return array(
            'email' => 'required|email|unique:users,email,' . $this->user_id . ',id',
            'first_name' => 'required',
            'last_name' => 'required',
            'timezone' => 'required',
            'language_id' => 'required',
            'password' => 'required|min:5',
        );
    }

    /**
     * The rules for validation via api.
     *
     * @var array
     */
    public function apiUpdateRules($request)
    {
        $arr = [];

        if (isset($request->email)) {
            $arr['email'] = 'required|email|unique:users,email,' . $this->user_id . ',id';
        }
        if (isset($request->first_name)) {
            $arr['first_name'] = 'required';
        }
        if (isset($request->last_name)) {
            $arr['last_name'] = 'required';
        }
        if (isset($request->timezone)) {
            $arr['timezone'] = 'required';
        }
        if (isset($request->language_id)) {
            $arr['language_id'] = 'required';
        }
        if (isset($request->password)) {
            $arr['password'] = 'min:5';
        }

        return $arr;
    }

    public function getSubscriberCountByStatus($status)
    {
        // @note: in this particular case, a simple count(distinct) query is much more efficient
        $query = $this->subscribers()->where('subscribers.status', $status)->distinct('subscribers.email');

        return $query->count();
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'SubscriberCount' => function (&$customer) {
                return $customer->subscribersCount(false);
            },
            'SubscriberUsage' => function (&$customer) {
                return $customer->subscribersUsage(true);
            },
            'SubscribedCount' => function (&$customer) {
                return $customer->getSubscriberCountByStatus(Subscriber::STATUS_SUBSCRIBED);
            },
            'UnsubscribedCount' => function (&$customer) {
                return $customer->getSubscriberCountByStatus(Subscriber::STATUS_UNSUBSCRIBED);
            },
            'UnconfirmedCount' => function (&$customer) {
                return $customer->getSubscriberCountByStatus(Subscriber::STATUS_UNCONFIRMED);
            },
            'BlacklistedCount' => function (&$customer) {
                return $customer->getSubscriberCountByStatus(Subscriber::STATUS_BLACKLISTED);
            },
            'SpamReportedCount' => function (&$customer) {
                return $customer->getSubscriberCountByStatus(Subscriber::STATUS_SPAM_REPORTED);
            },
            'MailListSelectOptions' => function (&$customer) {
                return $customer->getMailListSelectOptions([], true);
            },

        ];

        // retrieve cached data
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            $cache = [];
        }

        if (is_null($key)) {
            // update all cache
            foreach ($index as $key => $callback) {
                $cache[$key] = $callback($this);
                if ($key == 'SubscriberCount') {
                    // SubscriberCount cache must always be updated as its value will be used for the others
                    $this->cache = json_encode($cache);
                    $this->save();
                }
            }
        } else {
            // update specific key
            $callback = $index[$key];
            $cache[$key] = $callback($this);
        }

        // write back to the DB
        $this->cache = json_encode($cache);
        $this->save();
    }

    /**
     * Retrieve Campaign cached data.
     *
     * @return mixed
     */
    public function readCache($key, $default = null)
    {
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            return $default;
        }
        if (array_key_exists($key, $cache)) {
            if (is_null($cache[$key])) {
                return $default;
            } else {
                return $cache[$key];
            }
        } else {
            return $default;
        }
    }

    /**
     * Sending servers count.
     *
     * @var int
     */
    public function sendingServersCount()
    {
        return $this->sendingServers()->count();
    }

    /**
     * Sending domains count.
     *
     * @var int
     */
    public function sendingDomainsCount()
    {
        return $this->sendingDomains()->count();
    }

    /**
     * Get max sending server count.
     *
     * @var int
     */
    public function maxSendingServers()
    {
        $count = $this->getOption('sending_servers_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Get max email verification server count.
     *
     * @var int
     */
    public function maxEmailVerificationServers()
    {
        $count = $this->getOption('email_verification_servers_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Calculate email verification server usage.
     *
     * @return number
     */
    public function emailVerificationServersUsage()
    {
        $max = $this->maxEmailVerificationServers();
        $count = $this->emailVerificationServersCount();

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    /**
     * Calculate email verigfication servers usage.
     *
     */
    public function displayEmailVerificationServersUsage()
    {
        if ($this->maxEmailVerificationServers() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->emailVerificationServersUsage() . '%';
    }

    /**
     * Calculate sending servers usage.
     *
     * @return number
     */
    public function sendingServersUsage()
    {
        $max = $this->maxSendingServers();
        $count = $this->sendingServersCount();

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    /**
     * Calculate sending servers usage.
     *
     */
    public function displaySendingServersUsage()
    {
        if ($this->maxSendingServers() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->sendingServersUsage() . '%';
    }

    /**
     * Get max sending server count.
     *
     */
    public function maxSendingDomains()
    {
        $count = $this->getOption('sending_domains_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Calculate subscibers usage.
     *
     * @return number
     */
    public function sendingDomainsUsage()
    {
        $max = $this->maxSendingDomains();
        $count = $this->sendingDomainsCount();

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    /**
     * Calculate subscibers usage.
     *
     */
    public function displaySendingDomainsUsage()
    {
        if ($this->maxSendingDomains() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->sendingDomainsUsage() . '%';
    }

    /**
     * Count customer automations.
     *
     * @return number
     */
    public function automationsCount()
    {
        return $this->automation2sCount();
    }

    /**
     * Get max automation count.
     *
     * @var int
     */
    public function maxAutomations()
    {
        $count = $this->getOption('automation_max');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Calculate subscibers usage.
     *
     * @return number
     */
    public function automationsUsage()
    {
        $max = $this->maxAutomations();
        $count = $this->automation2sCount();

        if ($max == '∞') {
            return 0;
        }
        if ($max == 0) {
            return 0;
        }
        if ($count > $max) {
            return 100;
        }

        return round((($count / $max) * 100), 2);
    }

    /**
     * Calculate subscibers usage.
     *
     * @return number
     */
    public function displayAutomationsUsage()
    {
        if ($this->maxAutomations() == '∞') {
            return trans('messages.unlimited');
        }

        return $this->automationsUsage() . '%';
    }

    /**
     * Check if customer has admin account.
     *
     * @return bool
     */
    public function hasAdminAccount()
    {
        return is_object($this->user) && is_object($this->user->admin);
    }

    public function activeSendingServers()
    {
        return $this->sendingServers()->where('status', '=', SendingServer::STATUS_ACTIVE);
    }

    /**
     * Check if customer is disabled.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Check if customer is disabled.
     *
     * @return bool
     */
    public function currentPlanName()
    {
        return is_object($this->getActiveShopifyRecurringApplicationCharge()) ?
            $this->getActiveShopifyRecurringApplicationCharge()->plan->name :
            '';
    }

    /**
     * Get total file size usage.
     *
     * @return number
     */
    public function totalUploadSize()
    {
        return Tool::getDirectorySize(base_path('public/source/' . $this->user->uid)) / 1048576;
    }

    /**
     * Get max upload size quota.
     *
     */
    public function maxTotalUploadSize()
    {
        $count = $this->getOption('max_size_upload_total');
        if ($count == -1) {
            return '∞';
        } else {
            return $count;
        }
    }

    /**
     * Calculate campaign usage.
     *
     */
    public function totalUploadSizeUsage()
    {
        if ($this->maxTotalUploadSize() == '∞') {
            return 0;
        }
        if ($this->maxTotalUploadSize() == 0) {
            return 100;
        }

        return round((($this->totalUploadSize() / $this->maxTotalUploadSize()) * 100), 2);
    }

    /**
     * Custom can for customer.
     *
     * @return bool
     */
    public function can($action, $item)
    {
        return $this->user->can($action, [$item, 'customer']);
    }

    /**
     * Custom name + email.
     *
     * @return string
     */
    public function displayNameEmail()
    {
        return $this->displayName() . ' (' . $this->user->email . ')';
    }

    /**
     * Get customer contact.
     *
     * @return Contact
     */
    public function getContact()
    {
        if (is_object($this->contact)) {
            $contact = $this->contact;
        } else {
            $contact = new Contact([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->user->email,
            ]);
        }

        return $contact;
    }

    /*
     * Custom name + email.
     *
     * @return string
     */
    public function displayNameEmailOption()
    {
        return $this->displayName() . '|||' . $this->user->email;
    }

    /**
     * Email verification servers count.
     *
     * @var int
     */
    public function emailVerificationServersCount()
    {
        return $this->emailVerificationServers()->count();
    }

    /**
     * Get list of available email verification servers.
     *
     * @var bool
     */
    public function getEmailVerificaionServers()
    {
        // Check the customer has permissions using email verification servers and has his own email verification servers
        if ($this->getOption('create_email_verification_servers') == 'yes') {
            return $this->activeEmailVerificationServers()->get()->map(function ($server) {
                return $server;
            });
            // If customer dont have permission creating sending servers
        } else {
            // Get server from the plan
            return $this->getActiveShopifyRecurringApplicationCharge()->plan->getEmailVerificaionServers();
        }
    }

    /**
     * Get list of available sending domains.
     *
     * @var bool
     */
    public function getSendingDomains()
    {
        $result = [];

        // Check the customer has permissions using sending domains and has his own sending domains
        if ($this->getOption('create_sending_domains') == 'yes') {
            $result = $this->activeSendingDomains()->get()->map(function ($server) {
                return $server;
            });
            // If customer dont have permission creating sending domain
        } else {
            // Check if has email verification servers for current subscription
            $result = SendingDomain::getAllAdminActive()->get()->map(function ($server) {
                return $server;
            });
        }

        return $result;
    }

    /**
     * Get list of available sending domains options.
     *
     * @var bool
     */
    public function getSendingDomainOptions()
    {
        return $this->getSendingDomains()->map(function ($domain) {
            return ['value' => $domain->uid, 'text' => $domain->name];
        });
    }

    /**
     * Get the list of available mail lists, used for populating select box.
     *
     * @return array
     */
    public function getMailListSelectOptions($options = [], $cache = false)
    {
        $query = MailList::getAll();
        $query->where('customer_id', '=', $this->id);

        # Other list
        if (isset($options['other_list_of'])) {
            $query->where('id', '!=', $options['other_list_of']);
        }

        return $query->orderBy('name')->get()->map(function ($item) use ($cache) {
            return [
                'id' => $item->id,
                'value' => $item->uid,
                'text' => $item->name . ' (' . $item->subscribersCount($cache) . ' ' . strtolower(trans('messages.subscribers')) . ')'
            ];
        });
    }

    public function getSegment2SelectOptions($options = [])
    {
        $query = Segment2::query()->where('customer_id', '=', $this->id);

        # Other list
        if (isset($options['other_list_of'])) {
            $query->where('id', '!=', $options['other_list_of']);
        }

        return $query->orderBy('name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'value' => $item->id,
                'text' => $item->name
            ];
        });
    }

    /**
     * Get email verification servers select options.
     *
     * @return array
     */
    public function emailVerificationServerSelectOptions()
    {
        $servers = $this->getEmailVerificaionServers();
        $options = [];
        foreach ($servers as $server) {
            $options[] = ['text' => $server->name, 'value' => $server->uid];
        }

        return $options;
    }

    /**
     * Get customer's sending servers type.
     *
     * @return array
     */
    public function getSendingServertypes()
    {
        $allTypes = SendingServer::types();
        $types = [];

        foreach ($allTypes as $type => $server) {
            if ($this->isAllowCreateSendingServerType($type)) {
                $types[$type] = $server;
            }
        }

        if (!Setting::isYes('delivery.sendmail')) {
            unset($types['sendmail']);
        }

        if (!Setting::isYes('delivery.phpmail')) {
            unset($types['php-mail']);
        }

        return $types;
    }

    /**
     * Check customer can create sending servers type.
     *
     * @return bool
     */
    public function isAllowCreateSendingServerType($type)
    {
        $customerTypes = $this->getOption('sending_server_types');
        if ($this->getOption('all_sending_server_types') == 'yes' ||
            (isset($customerTypes[$type]) && $customerTypes[$type] == 'yes')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get import jobs.
     *
     */
    public function getImportBlacklistJobs()
    {
        return SystemJob::where('name', '=', "Acelle\Jobs\ImportBlacklistJob")
            ->where('data', 'like', '%"customer_id":' . $this->id . '%');
    }

    /**
     * Get running import jobs.
     *
     */
    public function getActiveImportBlacklistJobs()
    {
        return $this->getImportBlacklistJobs()
            ->where('status', '!=', SystemJob::STATUS_DONE)
            ->where('status', '!=', SystemJob::STATUS_FAILED)
            ->where('status', '!=', SystemJob::STATUS_CANCELLED);
    }

    /**
     * Get last import black list job.
     *
     */
    public function getLastActiveImportBlacklistJob()
    {
        return $this->getActiveImportBlacklistJobs()
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Add email to customer's blacklist.
     */
    public function addEmaillToBlacklist($email)
    {
        $email = trim(strtolower($email));

        if (Tool::isValidEmail($email)) {
            $exist = $this->blacklists()->where('email', '=', $email)->count();
            if (!$exist) {
                $blacklist = new Blacklist();
                $blacklist->customer_id = $this->id;
                $blacklist->email = $email;
                $blacklist->save();
            }
        }
    }

    /**
     * Get all customer's and system domains.
     *
     */
    public function getAllSystemAndOwnDomains()
    {
        return SendingDomain::getAllActive();
    }

    //====================================== BILLABLE USER INTERFACE =======================================

    /**
     * Customer get email.
     *
     * @return string
     */
    public function getBillableEmail()
    {
        return $this->email();
    }

    /**
     * Customer get remote owner id.
     *
     * @return string
     */
    public function getBillableId()
    {
        return $this->uid;
    }

    /**
     * Customer get local owner id.
     *
     * @return string
     */
    public function getLocalOwnerId()
    {
        return $this->id;
    }

    /**
     * Update billable owner id.
     */
    public function updateRemoteOwnerId($ownerId)
    {
        $this->remote_owner_id = $ownerId;
        $this->save();
    }

    /**
     * Update billable card brand.
     */
    public function updateBillableCardBrand($cardBrand)
    {
        $this->billable_card_brand = $cardBrand;
        $this->save();
    }

    /**
     * Update billable card last four.
     */
    public function updateBillableCardLastFour($cardLastFour)
    {
        $this->billable_card_last_four = $cardLastFour;
        $this->save();
    }

    /**
     * Get customer options.
     *
     */
    public function getOptions()
    {
        if (is_object($this->getActiveShopifyRecurringApplicationCharge())) {
            // Find plan
            return $this->getActiveShopifyRecurringApplicationCharge()->plan->getOptions();
        } else {
            return [];
        }
    }

    /**
     * Get customer option.
     *
     * @return string
     */
    public function getOption($name)
    {
        $options = $this->getOptions();
        // @todo: chỗ này cần raise 1 cái exception chứ ko phải là trả về rỗng (trả vể rỗng để "dìm" lỗi đi à?)
        return isset($options[$name]) ? $options[$name] : null;
    }

    /**
     * Check if customer has api access.
     *
     * @return bool
     */
    public function canUseApi()
    {
        return $this->getOption('api_access') == 'yes';
    }

    /**
     * Get all verified identities.
     *
     * @return array
     */
    public function getVerifiedIdentities()
    {
        $list = [];

        // own emails
        $senders = $this->allSenders()->where('status', Sender::STATUS_VERIFIED)->get();
        foreach ($senders as $sender) {
            $list[] = $sender->name . ' <' . $sender->email . '>';
        }

        // own domain
        $domains = $this->activeVerifiedSendingDomains()->get();
        foreach ($domains as $domain) {
            $list[] = $domain->name;
        }

        // plan sending server emails if system sending servers
        $plan = $this->getActiveShopifyRecurringApplicationCharge()->plan;
        if ($plan->useSystemSendingServer()) {
            $list = array_merge($plan->getVerifiedIdentities(), $list);
        }

        return array_values(array_unique($list));
    }

    /**
     * Get all verified identities.
     *
     * @return array
     */
    public function verifiedIdentitiesDroplist($keyword = null)
    {
        $droplist = [];
        $topList = [];
        $bottomList = [];

        if (!$keyword) {
            $keyword = '###';
        }

        foreach ($this->getVerifiedIdentities() as $item) {
            // check if email
            if (extract_email($item) !== null) {
                $email = extract_email($item);
                if (strpos(strtolower($email), $keyword) === 0) {
                    $topList[] = [
                        'text' => extract_name($item),
                        'value' => $email,
                        'desc' => str_replace($keyword, '<span class="text-semibold text-primary"><strong>' . $keyword . '</strong></span>', $email),
                    ];
                } else {
                    $bottomList[] = [
                        'text' => extract_name($item),
                        'value' => $email,
                        'desc' => $email,
                    ];
                }
            } else { // domains are alse
                $dKey = explode('@', $keyword);
                $eKey = $dKey[0];
                $dKey = isset($dKey[1]) ? $dKey[1] : null;
                // if ( (!isset($dKey) || $dKey == '') || ($dKey && strpos(strtolower($item), $dKey) === 0 )) {
                if ($keyword == '###') {
                    $eKey = '****';
                }
                $topList[] = [
                    'text' => $eKey . '@' . str_replace($dKey, '<span class="text-semibold text-primary"><strong>' . $dKey . '</strong></span>', $item),
                    'subfix' => $item,
                    'desc' => null,
                ];
                // }
            }
        }

        $droplist = array_merge($topList, $bottomList);

        return $droplist;
    }

    /**
     * Count customer automation2s.
     *
     * @return number
     */
    public function automation2sCount()
    {
        return $this->automation2s()->count();
    }

    /**
     * Assign plan to customer.
     */
    public function assignPlan($plan)
    {
        if ($this->activeSubscription()) {
            $this->activeSubscription()->cancelNow();
        }

        // assign new plan
        //$gateway = Cashier::getPaymentGateway();
        //$gateway->create($this, $plan);
    }

    /**
     * Check customer if has notice.
     */
    public function hasSubscriptionNotice()
    {
        //$gateway = Cashier::getPaymentGateway();

        if (is_object($this->subscription)) {
            return $this->subscription->hasError();
        }

        return false;
    }

    public function isSubscriptionActive()
    {
        $subscription = $this->activeSubscription();
        if (is_null($subscription)) {
            return false;
        }

        return $subscription->isActive();
    }

    public function getHomePath($path = '')
    {
        return $this->user->getHomePath($path);
    }

    public function getLockPath($path)
    {
        return $this->user->getLockPath($path);
    }

    public function allowUnverifiedFromEmailAddress()
    {
        $subscription = $this->getActiveShopifyRecurringApplicationCharge();
        if (!$subscription) return false;

        $plan = $subscription->plan;
        if (!$plan) return false;

        if ($plan->useSystemSendingServer() && is_object($plan->primarySendingServer())) {
            return $plan->primarySendingServer()->allowUnverifiedFromEmailAddress();
        }

        return true;
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function shopify_shop(): HasOne
    {
        return $this->hasOne(ShopifyShop::class, ShopifyShop::COLUMN_customer_id);
    }

    /**
     * Get list of available tracking domain options.
     *
     * @var bool
     */
    public function getVerifiedTrackingDomainOptions()
    {
        return $this->trackingDomains()->verified()->get()->map(function ($domain) {
            return ['value' => $domain->uid, 'text' => $domain->name];
        });
    }

    public function usageCharges(): HasMany
    {
        return $this->hasMany(ShopifyUsageCharge::class, ShopifyUsageCharge::COLUMN_customer_id);
    }

    public function shopifyUsageChargeEntries(): HasMany
    {
        return $this->hasMany(ShopifyUsageChargeEntry::class, ShopifyUsageChargeEntry::COLUMN_customer_id);
    }

    public function shopifyRecurringApplicationCharges(): HasMany
    {
        return $this->hasMany(ShopifyRecurringApplicationCharge::class, ShopifyRecurringApplicationCharge::COLUMN_customer_id);
    }

    /**
     * @return ShopifyRecurringApplicationCharge|null
     */
    public function getActiveShopifyRecurringApplicationCharge()
    {
        return $this->shopifyRecurringApplicationCharges()
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function shopifyReviews(): HasMany
    {
        return $this->hasMany(ShopifyReview::class, ShopifyReview::COLUMN_customer_id);
    }

    function salesChannels(): HasOne
    {
        return $this->hasOne(SalesChannels::class, SalesChannels::COLUMN_customer_id);
    }

    function getShopifyMailingList(): ?MailList
    {
        if ($this->shopify_shop && $this->shopify_shop->mail_list)
            return $this->shopify_shop->mail_list;
        return null;
    }

    function getDefaultMailingList(): ?MailList
    {
        if ($this->shopify_shop && $this->shopify_shop->mail_list)
            return $this->shopify_shop->mail_list;
        if (count($this->lists))
            return $this->lists[0];
        return null;
    }

    function getSegment2Options($list, $strict = false)
    {
        $default_list = $this->getDefaultMailingList();
        if ($strict) {
            if (!($list instanceof MailList)
                || !($default_list instanceof MailList)
                || $list->uid != $default_list->uid)
                return [];
        }

        $options = [];
        foreach ($this->segment2s as $item)
            $options[] = ['value' => $item->uid, 'text' => $item->name];

        return $options;
    }

    function createDefaultContact()
    {
        if ($this->contact_id && $this->contact) return;
        if (!$this->shopify_shop) return;

        $h = new ShopifyHelper(null);
        $shop_object = $h->getShopObject($this->shopify_shop->myshopify_domain, $this->shopify_shop->access_token);
        $country = Country::findCountryByCode($shop_object->country_code);

        $contact = new Contact();
        $contact->email = $shop_object->email;
        $contact->first_name = $shop_object->shop_owner ?: "";
        $contact->last_name = "";
        $contact->address_1 = $shop_object->address1 ?: "";
        $contact->address_2 = $shop_object->address2 ?: "";
        $contact->city = $shop_object->city ?: "";
        $contact->state = $shop_object->province ?: "";
        $contact->zip = $shop_object->zip ?: "";
        $contact->phone = $shop_object->phone ?: "";
        $contact->url = "https://" . $shop_object->domain;
        $contact->company = $shop_object->name ?: "";
        $contact->country_id = $country ? $country->id : 221;
        $contact->save();
        $this->contact_id = $contact->id;
        $this->save();
    }
}
