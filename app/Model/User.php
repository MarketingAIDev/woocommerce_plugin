<?php

/**
 * User class.
 *
 * Model class for user
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

use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Notifications\ResetPassword;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Class User
 * @package Acelle\Model
 *
 * @property int id
 * @property string uid
 * @property string api_token
 * @property integer|null creator_id
 * @property string email
 * @property string password
 * @property string remember_token
 * @property string|null status
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property mixed quota
 * @property boolean activated
 * @property string first_name
 * @property string last_name
 * @property string timezone
 * @property string affiliation_id
 *
 * @property Customer[] customers
 * @property FcmToken[] fcm_tokens
 */
class User extends Authenticatable
    // 5.7 implements MustVerifyEmail
{
    use Notifiable;

    const ASSET_DIR = 'files';

    const COLUMN_api_token = 'api_token';
    const COLUMN_creator_id = 'creator_id';
    const COLUMN_email = 'email';
    const COLUMN_password = 'password';
    const COLUMN_remember_token = 'remember_token';
    const COLUMN_status = 'status';
    const COLUMN_quota = 'quota';
    const COLUMN_activated = 'activated';
    const COLUMN_first_name = 'first_name';
    const COLUMN_last_name = 'last_name';
    const COLUMN_timezone = 'timezone';
    const COLUMN_affiliation_id = 'affiliation_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::COLUMN_first_name,
        self::COLUMN_last_name,
        self::COLUMN_timezone,
        self::COLUMN_email,
        self::COLUMN_password,
        self::COLUMN_activated,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        self::COLUMN_password,
        self::COLUMN_remember_token,
        self::COLUMN_affiliation_id
    ];

    public static function registerRules()
    {
        return [
            self::COLUMN_first_name => 'required',
            self::COLUMN_last_name => 'required',
            self::COLUMN_timezone => 'required|timezone',
            self::COLUMN_email => 'required|email|unique:users',
            self::COLUMN_password => 'required|min:5|confirmed',
        ];
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function chat_canned_messages()
    {
        return $this->hasMany(ChatCannedMessage::class);
    }

    public function chat_departments()
    {
        return $this->hasMany(ChatDepartment::class);
    }

    public function chat_sessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    public function chat_messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * @return int[]
     */
    public function customerIds()
    {
        $ids = [];
        foreach ($this->customers as $customer)
            $ids[] = $customer->id;
        return $ids;
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function systemJobs()
    {
        return $this->hasMany(SystemJob::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get authenticate from file.
     */
    public static function getAuthenticateFromFile()
    {
        $path = base_path('.authenticate');

        if (file_exists($path)) {
            $content = \File::get($path);
            $lines = explode("\n", $content);
            if (count($lines) > 1) {
                $demo = session()->get('demo');
                if (!isset($demo) || $demo == 'backend') {
                    return ['email' => $lines[0], 'password' => $lines[1]];
                } else {
                    return ['email' => $lines[2], 'password' => $lines[3]];
                }
            }
        }

        return ['email' => '', 'password' => ''];
    }

    /**
     * Send regitration activation email.
     */
    public function sendActivationMail($name = null)
    {
        $layout = Layout::where('alias', 'registration_confirmation_email')->first();
        $token = $this->getToken();

        $layout->content = str_replace('{ACTIVATION_URL}', join_url(config('app.url'), action('UserController@activate', ['token' => $token], false)), $layout->content);
        $layout->content = str_replace('{CUSTOMER_NAME}', $name, $layout->content);

        $name = is_null($name) ? trans('messages.to_email_name') : $name;
        \Mail::to(json_decode(json_encode(['email' => $this->email, 'name' => $name])))->send(new \Acelle\Mail\RegistrationConfirmationMailer($layout->content, $layout->subject));
    }

    public function sendWelcomeMail()
    {
        $name = $this->first_name . $this->last_name;
        $layout = Layout::where('alias', 'welcome_email')->first();
        $layout->content = str_replace('{CUSTOMER_NAME}', $name, $layout->content);
        \Mail::to(json_decode(json_encode(['email' => $this->email, 'name' => $name])))->send(new \Acelle\Mail\RegistrationConfirmationMailer($layout->content, $layout->subject));
    }

    /**
     * User activation.
     */
    public function userActivation()
    {
        return $this->hasOne(UserActivation::class);
    }

    /**
     * Create activation token for user.
     *
     * @return string
     */
    public function getToken()
    {
        $token = UserActivation::getToken();

        $userActivation = $this->userActivation;

        if (!is_object($userActivation)) {
            $userActivation = new UserActivation();
            $userActivation->user_id = $this->id;
        }

        $userActivation->token = $token;
        $userActivation->save();

        return $token;
    }

    /**
     * Set user is activated.
     */
    public function setActivated()
    {
        $this->activated = true;
        $this->save();
    }

    /**
     * @param $uid
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Check if user has admin account.
     */
    public function isAdmin()
    {
        return is_object($this->admin);
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token, react_route('/password/reset/' . $token)));
    }

    public function getStoragePath($path = '')
    {
        $base = storage_path('app/users/' . $this->uid);

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    public function getLockPath($path)
    {
        $base = $this->getHomePath('locks');

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    public function getHomePath($path = '')
    {
        $base = $this->getStoragePath('home');

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        $full_path = join_paths($base, $path);
        if (!\File::exists($full_path)) {
            \File::makeDirectory($full_path, 0777, true, true);
        }
        return $full_path;
    }

    public function uploadAsset($file)
    {
        // store to storage/app/users/{uid}/home/files/
        $assetdir = $this->getHomePath(self::ASSET_DIR);
        $newname = $file->getClientOriginalName();
        $newpath = join_paths($assetdir, $newname);

        // In case file name conflicts
        $i = 1;
        while (file_exists($newpath)) {
            $newname = $file->getClientOriginalName() . "_" . $i;
            $newpath = join_paths($assetdir, $newname);
            ++$i;
        }

        // Move uploaded file
        $file->move(
            $assetdir,
            $newname
        );

        return $newname;
    }

    function fcm_tokens(): HasMany
    {
        return $this->hasMany(FcmToken::class, FcmToken::COLUMN_user_id);
    }

    /**
     * @return Customer[]
     */
    static function getAdminCustomers(): array
    {
        $result = [];

        $admins = Admin::all();
        foreach ($admins as $admin) {
            $customers = $admin->user->customers;
            foreach ($customers as $customer) {
                $result[] = $customer;
            }
        }
        return $result;
    }

    function triggerAdditionalShopAddedAutomation()
    {
        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_ADDITIONAL_SHOP_ADDED);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    function createAdminSubscribers()
    {
        foreach (self::getAdminCustomers() as $customer) {
            $list = $customer->getShopifyMailingList();
            if ($list) {
                $exists = $list
                        ->subscribers()
                        ->where('email', '=', $this->email)
                        ->count() != 0;
                if (!$exists)
                    Subscriber::createSubscriber($list,
                        $this->email,
                        $this->first_name,
                        $this->last_name
                    );
            }
        }
    }

    function removeAdminSubscribers()
    {
        foreach (self::getAdminCustomers() as $customer) {
            $list = $customer->getShopifyMailingList();
            if ($list)
                $list->subscribers()
                    ->where('email', '=', $this->email)
                    ->delete();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (User::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Add api token
            $item->api_token = Str::random(60);
        });

        static::deleting(function (self $item) {
            // $item->removeAdminSubscribers();
        });
    }
}
