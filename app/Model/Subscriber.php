<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Log as MailLog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class Subscriber
 * @package Acelle\Model
 *
 * @property integer id
 * @property string uid
 * @property integer mail_list_id
 * @property MailList mailList
 * @property string email
 * @property string status
 * @property string from
 * @property string ip
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property string subscription_type
 * @property string tags
 * @property integer shopify_customer_id
 *
 * @property EmailVerification[] emailVerifications
 * @property EmailVerification emailVerification
 * @property SubscriberField[] subscriberFields
 * @property TrackingLog[] trackingLogs
 * @property UnsubscribeLog[] unsubscribeLogs
 *
 * @property ShopifyCustomer shopify_customer
 * @property ShopifyReview[] shopify_reviews
 * @property ChatSession[] chat_sessions
 */
class Subscriber extends Model
{
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_BLACKLISTED = 'blacklisted';
    const STATUS_SPAM_REPORTED = 'spam-reported';
    const STATUS_UNCONFIRMED = 'unconfirmed';

    const SUBSCRIPTION_TYPE_DOUBLE_OPTIN = 'double';
    const SUBSCRIPTION_TYPE_SINGLE_OPTIN = 'single';
    const SUBSCRIPTION_TYPE_IMPORTED = 'imported';

    protected $dates = ['unsubscribed_at'];

    public static $rules = [
        'email' => ['required', 'email'],
    ];

    protected $appends = ['extra_stats'];

    public static $CAMPAIGN_FOR_EXTRA_STATS = null;

    public function getExtraStatsAttribute()
    {
        if (empty(self::$CAMPAIGN_FOR_EXTRA_STATS)) return null;
        return [
            "last_click_at" => $this->lastClickLog(self::$CAMPAIGN_FOR_EXTRA_STATS)->created_at ?? null,
            "click_count" => $this->clickLogs(self::$CAMPAIGN_FOR_EXTRA_STATS)->count() ?? 0,
            "last_open_at" => $this->lastOpenLog(self::$CAMPAIGN_FOR_EXTRA_STATS)->created_at ?? null,
            "open_count" => $this->openLogs(self::$CAMPAIGN_FOR_EXTRA_STATS)->count() ?? 0,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mail_list_id', 'email',
        'image',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $fields_rules = array();


    // region Relations
    public function mailList()
    {
        return $this->belongsTo(MailList::class);
    }

    public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class);
    }

    public function emailVerification()
    {
        return $this->hasOne(EmailVerification::class);
    }

    /* This looks like a mistake.
     * A `belongsTo` relation means that the foreign key field
     * is present on the table for THIS model. But it is not.
     * public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }*/

    function segment2s(): BelongsToMany
    {
        return $this->belongsToMany(Segment2::class);
    }

    public function subscriberFields(): HasMany
    {
        return $this->hasMany(SubscriberField::class);
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }

    public function unsubscribeLogs(): HasMany
    {
        return $this->hasMany(UnsubscribeLog::class);
    }

    function shopify_customer(): HasOne
    {
        return $this->hasOne(ShopifyCustomer::class, ShopifyCustomer::COLUMN_subscriber_id);
    }

    function shopify_reviews(): HasMany
    {
        return $this->hasMany(ShopifyReview::class, ShopifyReview::COLUMN_subscriber_id);
    }

    function chat_sessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, ChatSession::COLUMN_subscriber_id);
    }

    // endregion

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
            while (Subscriber::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Get rules.
     *
     * @var array
     */
    public function getRules()
    {
        $rules = $this->mailList->getFieldRules();
        $item_id = isset($this->id) ? $this->id : 'NULL';
        $rules['EMAIL'] = $rules['EMAIL'] . '|unique:subscribers,email,' . $item_id . ',id,mail_list_id,' . $this->mailList->id;

        return $rules;
    }

    /**
     * Blacklist a subscriber.
     *
     * @return bool
     */
    public function sendToBlacklist($reason = null)
    {
        // blacklist all email
        self::where('email', $this->email)->update(['status' => self::STATUS_BLACKLISTED]);

        // create an entry in blacklists table
        $r = Blacklist::firstOrNew(['email' => $this->email]);
        $r->reason = $reason;
        $r->save();

        return true;
    }

    /**
     * Mark a subscriber/list as abuse-reported.
     *
     * @return bool
     */
    public function markAsSpamReported()
    {
        $this->status = self::STATUS_SPAM_REPORTED;
        $this->save();

        return true;
    }

    /**
     * Unsubscribe to the list.
     */
    public function unsubscribe()
    {
        $this->status = 'unsubscribed';
        $this->save();
        $this->unsubscribeLogs()->create([
            //
        ]);
    }

    /**
     * Update fields from request.
     */
    public function updateFields($params)
    {
        foreach ($params as $tag => $value) {
            $field = $this->mailList->getFieldByTag(str_replace('[]', '', $tag));
            if (is_object($field)) {
                $fv = SubscriberField::where('subscriber_id', '=', $this->id)->where('field_id', '=', $field->id)->first();
                if (!is_object($fv)) {
                    $fv = new SubscriberField();
                    $fv->subscriber_id = $this->id;
                    $fv->field_id = $field->id;
                }
                if (is_array($value)) {
                    $fv->value = implode(',', $value);
                } else {
                    $fv->value = $value;
                }
                $fv->save();

                // update email attribute of subscriber
                if ($field->tag == 'EMAIL') {
                    $this->email = $fv->value;
                    $this->save();
                }
            }
        }
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($query, $request)
    {
        /* does not support searching on subscriber fields, for the sake of performance
        $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id')
            ->leftJoin('mail_lists', 'subscribers.mail_list_id', '=', 'mail_lists.id');
        */
        $query = $query->leftJoin('mail_lists', 'subscribers.mail_list_id', '=', 'mail_lists.id');

        // Email verification join
        $query = $query->leftJoin('email_verifications', 'email_verifications.subscriber_id', '=', 'subscribers.id');

        if (isset($request)) {
            // Keyword
            if (!empty(trim($request->keyword))) {
                foreach (explode(' ', trim($request->keyword)) as $keyword) {
                    $query = $query->where(function ($q) use ($keyword) {
                        $q->orwhere('subscribers.email', 'like', '%' . $keyword . '%');
                        /* does not support searching on subscriber fields, for the sake of performance
                        ->orWhere('subscriber_fields.value', 'like', '%'.$keyword.'%');
                        */
                    });
                }
            }

            // filters
            $filters = $request->filters;
            if (!empty($filters)) {
                if (!empty($filters['status'])) {
                    $query = $query->where('subscribers.status', '=', $filters['status']);
                }
                if (!empty($filters['verification_result'])) {
                    if ($filters['verification_result'] == 'unverified') {
                        $query = $query->whereNull('email_verifications.result');
                    } else {
                        $query = $query->where('email_verifications.result', '=', $filters['verification_result']);
                    }
                }
            }

            // outside filters
            if (!empty($request->status)) {
                $query = $query->where('subscribers.status', '=', $request->status);
            }
            if (!empty($request->verification_result)) {
                $query = $query->where('email_verifications.result', '=', $request->verification_result);
            }

            // Open
            if ($request->open == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('open_logs')
                        ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Not Open
            if ($request->open == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('open_logs')
                        ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Click
            if ($request->click == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('click_logs')
                        ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Not Click
            if ($request->click == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('click_logs')
                        ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }
        }

        return $query;
    }

    /**
     * Get all languages.
     *
     * @return collect
     */
    public static function search($request, $customer = null)
    {
        $query = self::select('subscribers.*', 'email_verifications.result as verify_result');

        // Filter by customer
        if (!isset($customer)) {
            $customer = $request->selected_customer;
        }
        $query = $query->where('mail_lists.customer_id', '=', $customer->id);

        // Filter
        $query = self::filter($query, $request);

        // Order
        if (isset($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * Get all languages.
     *
     * @return collect
     */
    public static function gSearch($collect, $request, $customer = null)
    {
        $query = $collect->select('subscribers.*', 'email_verifications.result as verify_result');

        // Filter by customer
        if (!isset($customer)) {
            $customer = $request->selected_customer;
        }
        $query = $query->where('mail_lists.customer_id', '=', $customer->id);

        // Filter
        $query = self::filter($query, $request);

        // Order
        if (isset($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * Get field value by list field.
     *
     * @return value
     */
    public function getValueByField($field)
    {
        $fv = $this->subscriberFields->filter(function ($r, $key) use ($field) {
            return $r->field_id == $field->id;
        })->first();
        if (is_object($fv)) {
            return $fv->value;
        } else {
            return '';
        }
    }

    /**
     * Get field value by list field.
     *
     * @return value
     */
    public function getValueByTag($tag)
    {
        $fv = SubscriberField::leftJoin('fields', 'fields.id', '=', 'subscriber_fields.field_id')
            ->where('subscriber_id', '=', $this->id)->where('fields.tag', '=', $tag)->first();
        if (is_object($fv)) {
            return $fv->value;
        } else {
            return '';
        }
    }

    /**
     * Set field.
     *
     * @return value
     */
    public function setField($field, $value)
    {
        $fv = SubscriberField::where('subscriber_id', '=', $this->id)->where('field_id', '=', $field->id)->first();
        if (!is_object($fv)) {
            $fv = new SubscriberField();
            $fv->field_id = $field->id;
            $fv->subscriber_id = $this->id;
        }

        $fv->value = $value;
        $fv->save();
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Import subscribers from file.
     *
     * @return object
     */
    public static function import($user, $list)
    {
        $customer = request()->selected_customer;
        $directory = storage_path('import/');

        // Import to database
        $filename = $list->id . '-data.csv';
        $content = \File::get($directory . $filename);
        $lines = preg_split('/\r\n|\r|\n/', $content); // explode("\n\r", $content);
        $total = count($lines) - 1;
        $success = 0;
        $error = 0;
        $lines_per_second = 1;
        $headers = explode(',', $lines[0]);
        $header_names = explode(',', $lines[0]);

        // update header tags
        foreach ($headers as $key => $tag) {
            $tag = trim(strtoupper(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($tag)))));
            $headers[$key] = $tag;
            if ($tag == 'EMAIL') {
                $main_index = $key;
            }
        }

        // check valid file
        if (!in_array('EMAIL', $headers)) {
            $str = "error\n<span style='color:red'>" . trans('messages.invalid_csv_file') . '</span>';
            $str .= "\n0";
            $bytes_written = \File::put($directory . $list->id . '-process.log', $str);

            return;
        }

        $content_cache = '';
        $count = '0';
        foreach ($lines as $key => $line) {

            // authorize
            if ($user->cannot('create', new \Acelle\Model\Subscriber(['mail_list_id' => $list->id]))) {
                $str = "error\n<span style='color:red'>" . trans('messages.error_add_max_quota') . '</span><br />' . $content_cache;
                $str .= "\n" . $count;
                $bytes_written = \File::put($directory . $list->id . '-process.log', $str);

                // Action Log
                $list->log('import_max_error', $customer, ['count' => $count]);

                $error_detail = trans('messages.error_add_max_quota');
                $myfile = file_put_contents($directory . $list->uid . '-detail.log', $error_detail . PHP_EOL, FILE_APPEND | LOCK_EX);

                return;
            }

            if ($key > 0) {
                $parts = explode(',', $line);
                if (isset($parts[$main_index])) {
                    $email = strtolower(trim(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($parts[$main_index])))));
                } else {
                    $email = '';
                }

                $valid = $list->checkExsitEmail($email);
                if ($valid) {
                    //// save subscribers
                    $subscriber = new \Acelle\Model\Subscriber();
                    $subscriber->mail_list_id = $list->id;
                    $subscriber->email = $email;
                    $subscriber->status = 'subscribed';
                    $subscriber->save();

                    foreach ($parts as $key => $value) {
                        $value = trim(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($value))));
                        if (isset($headers[$key])) {
                            $lf = $list->fields()->where('tag', '=', $headers[$key])->first();
                            if (is_object($lf)) {
                                //// save fields
                                $lfv = new \Acelle\Model\SubscriberField(array(
                                    'field_id' => $lf->id,
                                    'subscriber_id' => $subscriber->id,
                                    'value' => $value,
                                ));
                                $lfv->save();
                            }
                        }
                    }

                    ++$success;
                    $error_detail = trans('messages.email_imported', ['time' => \Carbon\Carbon::now()->timezone(request()->selected_customer->timezone)->format(trans('messages.datetime_format_2')), 'email' => $email]);
                } else {
                    ++$error;
                    $error_detail = trans('messages.email_existed_invalid', ['time' => \Carbon\Carbon::now()->timezone(request()->selected_customer->timezone)->format(trans('messages.datetime_format_2')), 'email' => $email]);
                }
                if ($key % $lines_per_second == 0) {
                    $content_cache = trans('messages.import_export_statistics_line', [
                        'total' => $total,
                        'processed' => $success + $error,
                        'success' => $success,
                        'error' => $error,
                    ]); //"Total of ".$total." subscribers; ".($success+$error)." have been processed; ".$success." successfully and ".$error." with errors.";
                    $count = round((($success + $error) / $total) * 100, 0);
                    $str = "processing\n" . $content_cache;
                    $str .= "\n" . $count . '';
                    $bytes_written = \File::put($directory . $list->id . '-process.log', $str);

                    // Details
                    $myfile = file_put_contents($directory . $list->uid . '-detail.log', $error_detail . "\r\n" . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            }
        }

        $content_cache = trans('messages.import_export_statistics_line', [
            'total' => $total,
            'processed' => $success + $error,
            'success' => $success,
            'error' => $error,
        ]);
        $str = "finished\n" . $content_cache . "\n100";
        $bytes_written = \File::put($directory . $list->id . '-process.log', $str);

        // Action Log
        $list->log('import_success', $customer, ['count' => $success, 'error' => $error]);
    }

    /**
     * Export subscribers to csv.
     *
     * @return object
     */
    public static function export($user, $list)
    {
        $customer = request()->selected_customer;
        $directory = storage_path('export/');
        // Import to database
        $total = $list->subscribersCount(); // no cache
        $success = 0;
        $error = 0;
        $lines_per_second = 1;
        $data = [];
        $headers = [];
        foreach ($list->getFields as $key => $field) {
            $headers[] = $field->tag;
        }
        $headers = implode(',', $headers);

        foreach ($list->subscribers as $key => $item) {
            $cols = [];
            if (true) {
                if (true) {
                    foreach ($list->fields as $key => $field) {
                        $value = $item->getValueByField($field);
                        $cols[] = $value;
                    }
                    $data[] = implode(',', $cols);

                    ++$success;
                } else {
                    ++$error;
                }
                if ($key % $lines_per_second == 0) {
                    $content_cache = trans('messages.import_export_statistics_line', [
                        'total' => $total,
                        'processed' => $success + $error,
                        'success' => $success,
                        'error' => $error,
                    ]);
                    $str = "processing\n" . $content_cache;
                    $str .= "\n" . round((($success + $error) / $total) * 100, 0) . '';
                    $bytes_written = \File::put($directory . $list->id . '-process.log', $str);
                }
            }
        }

        $str = $headers . "\n" . implode("\n", $data);
        $bytes_written = \File::put($directory . $list->id . '-data.csv', $str);
        $content_cache = trans('messages.import_export_statistics_line', [
            'total' => $total,
            'processed' => $success + $error,
            'success' => $success,
            'error' => $error,
        ]);
        $str = "finished\n" . $content_cache . "\n100";
        $bytes_written = \File::put($directory . $list->id . '-process.log', $str);

        // Action Log
        $list->log('export_success', $customer, ['count' => $success, 'error' => $error]);
    }

    /**
     * Get secure code for updating subscriber.
     *
     * @param string $action
     */
    public function getSecurityToken($action)
    {
        $string = $this->email . $action . config('app.key');

        return md5($string);
    }

    /**
     * Create customer action log.
     *
     * @param string $cat
     * @param Customer $customer
     * @param array $add_datas
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'email' => $this->email,
            'list_id' => $this->mail_list_id,
            'list_uid' => $this->mailList->uid,
            'list_name' => $this->mailList->name,
        ];

        $data = array_merge($data, $add_datas);

        Log::create([
            'customer_id' => $customer->id,
            'type' => 'subscriber',
            'name' => $name,
            'data' => json_encode($data),
        ]);
    }

    /**
     * Copy to list.
     *
     * @param MailList $list
     */
    public function copy($list, $type = 'keep')
    {
        // find exists
        $copy = $list->subscribers()->where('email', '=', $this->email)->first();

        if (!is_object($copy)) {
            $copy = $this->replicate();
            $copy->mail_list_id = $list->id;
            unset($copy->verify_result);
            $copy->save();

            $copy = \Acelle\Model\Subscriber::find($copy->id);
        } else {
            // return if keep
            if ($type == 'keep') {
                return false;
            }
        }

        // update fields
        foreach ($this->subscriberFields as $item) {
            foreach ($copy->mailList->fields as $field) {
                if ($item->field->tag == $field->tag) {
                    $copy->setField($field, $item->value);
                }
            }
        }

        return $copy;
    }

    /**
     * Move to list.
     *
     * @param MailList $list
     */
    public function move($list, $type = 'keep')
    {
        $fromList = $this->mailList;
        $this->copy($list, $type);
        $this->delete();

        // update related campaign cache information
        $fromList->updateCachedInfo();
        $list->updateCachedInfo();
    }

    /**
     * Get tracking log.
     *
     * @param MailList $list
     */
    public function trackingLog($campaign)
    {
        $query = \Acelle\Model\TrackingLog::where('tracking_logs.subscriber_id', '=', $this->id);
        $query = $query->where('tracking_logs.campaign_id', '=', $campaign->id)->orderBy('created_at', 'desc')->first();

        return $query;
    }

    /**
     * Get all subscriber's open logs.
     *
     * @param MailList $list
     */
    public function openLogs($campaign = null)
    {
        $query = \Acelle\Model\OpenLog::leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.subscriber_id', '=', $this->id);

        if (isset($campaign)) {
            $query = $query->where('tracking_logs.campaign_id', '=', $campaign->id);
        }

        return $query;
    }

    /**
     * Get last open.
     *
     * @param MailList $list
     */
    public function lastOpenLog($campaign = null)
    {
        $query = $this->openLogs($campaign);

        $query = $query->orderBy('open_logs.created_at', 'desc')->first();

        return $query;
    }

    /**
     * Get all subscriber's click logs.
     *
     * @param MailList $list
     */
    public function clickLogs($campaign = null)
    {
        $query = \Acelle\Model\ClickLog::leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.subscriber_id', '=', $this->id);

        if (isset($campaign)) {
            $query = $query->where('tracking_logs.campaign_id', '=', $campaign->id);
        }

        return $query;
    }

    /**
     * Get last click.
     *
     * @param MailList $list
     */
    public function lastClickLog($campaign = null)
    {
        $query = $this->clickLogs();
        $query = $query->orderBy('click_logs.created_at', 'desc')->first();

        return $query;
    }

    /**
     * Update subscriber status.
     *
     * @param MailList $list
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Is overide copy/move subscriber.
     *
     * return array
     */
    public static function copyMoveExistSelectOptions()
    {
        return [
            ['text' => trans('messages.update_if_subscriber_exists'), 'value' => 'update'],
            ['text' => trans('messages.keep_if_subscriber_exists'), 'value' => 'keep'],
        ];
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Verify subscriber email address using a given service.
     */
    public function verify($verifier)
    {
        MailLog::info(sprintf('Start verifying %s (%s)', $this->email, $this->id));
        $log = new EmailVerification();
        list($result, $details) = $verifier->verify($this->email);
        $log->result = $result;
        $log->details = $details;
        $log->email_verification_server_id = $verifier->id;
        $this->emailVerifications()->save($log);
        MailLog::info(sprintf('Finish verifying %s (%s)', $this->email, $this->id));

        return $log->isDeliverable();
    }

    /**
     * Get email verification of a subcriber.
     *
     * @return EmailVerification
     */
    public function getEmailVerification()
    {
        return $this->emailVerifications()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get email verification of a subcriber.
     *
     * @return EmailVerification
     */
    public function getVerificationResult()
    {
        if (!$this->isVerifed()) {
            return '';
        }

        return $this->getEmailVerification()->result;
    }

    /**
     * Check if subscriber is verified.
     *
     * @return bool
     */
    public function isVerifed()
    {
        return is_object($this->getEmailVerification());
    }

    /**
     * Reset subscriber verification.
     */
    public function resetVerification()
    {
        return $this->emailVerifications()->delete();
    }

    /**
     * Upload and resize avatar.
     *
     * @return string
     * @var string
     *
     */
    public function uploadImage($file)
    {
        $path = 'app/subscriber/';
        $upload_path = storage_path($path);

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = $this->uid . '.jpg';

        // save to server
        $file->move($upload_path, $filename);

        // create thumbnails
        $img = \Image::make($upload_path . $filename);
        $img->fit(120, 120)->save($upload_path . $filename);

        return $path . $filename;
    }

    /**
     * Get image thumb path.
     *
     * @return string
     */
    public function imagePath()
    {
        if (!empty($this->uid) && is_file(storage_path('app/subscriber/' . $this->uid) . '.jpg')) {
            return storage_path('app/subscriber/' . $this->uid) . '.jpg';
        } else {
            return '';
        }
    }

    /**
     * Remove thumb path.
     */
    public function removeImage()
    {
        if (!empty($this->uid)) {
            $path = storage_path('app/subscriber/' . $this->uid);
            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($path . '.jpg')) {
                unlink($path . '.jpg');
            }
        }
    }

    /**
     * Check if the subscriber is listed in the Blacklist database.
     */
    public function isListedInBlacklist()
    {
        // @todo Filter by current user only
        return Blacklist::where('email', '=', $this->email)->exists();
    }

    public function getFullName($default = null)
    {
        $full = trim($this->getValueByTag('FIRST_NAME') . ' ' . $this->getValueByTag('LAST_NAME'));
        if (empty($full)) {
            return $default;
        } else {
            return $full;
        }
    }

    public function getFullNameOrEmail()
    {
        $full = $this->getFullName();
        if (empty($full)) {
            return $this->email;
        } else {
            return $full;
        }
    }

    /**
     * Is the subscriber active?
     */
    public function isActive()
    {
        return $this->status == self::STATUS_SUBSCRIBED;
    }

    /**
     * Get tags.
     */
    public function getTags()
    {
        if (!$this->tags) {
            return [];
        }

        return json_decode($this->tags, true);
    }

    /**
     * Get tags.
     */
    public function getTagOptions()
    {
        $arr = [];
        foreach ($this->getTags() as $tag) {
            $arr[] = ['text' => $tag, 'value' => $tag];
        }

        return $arr;
    }

    /**
     * Add tags.
     */
    public function addTags($arr)
    {
        $tags = $this->getTags();

        $nTags = array_values(array_unique(array_merge($tags, $arr)));

        $this->tags = json_encode($nTags);
        $this->save();
    }

    /**
     * Add tags.
     */
    public function updateTags($arr)
    {
        if (!$arr) {
            $arr = [];
        }

        // remove trailing space
        array_walk($arr, function (&$val) {
            $val = trim($val);
        });

        // remove empty tag
        $arr = array_filter($arr, function (&$val) {
            return !empty($val);
        });

        $this->tags = json_encode($arr);
        $this->save();
    }

    /**
     * Remove tag.
     */
    public function removeTag($tag)
    {
        $tags = $this->getTags();

        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
        }

        $this->tags = json_encode($tags);
        $this->save();
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function scopeFilter($query, $request)
    {
        /* does not support searching on subscriber fields, for the sake of performance
        $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id')
            ->leftJoin('mail_lists', 'subscribers.mail_list_id', '=', 'mail_lists.id');
        */
        $query = $query->leftJoin('mail_lists', 'subscribers.mail_list_id', '=', 'mail_lists.id');

        // Email verification join
        $query = $query->leftJoin('email_verifications', 'email_verifications.subscriber_id', '=', 'subscribers.id');

        if (isset($request)) {
            // Keyword
            if (!empty(trim($request->keyword))) {
                foreach (explode(' ', trim($request->keyword)) as $keyword) {
                    $query = $query->where(function ($q) use ($keyword) {
                        $q->orwhere('subscribers.email', 'like', '%' . $keyword . '%');
                        /* does not support searching on subscriber fields, for the sake of performance
                        ->orWhere('subscriber_fields.value', 'like', '%'.$keyword.'%');
                        */
                    });
                }
            }

            // filters
            $filters = $request->filters;
            if (!empty($filters)) {
                if (!empty($filters['status'])) {
                    $query = $query->where('subscribers.status', '=', $filters['status']);
                }
                if (!empty($filters['verification_result'])) {
                    if ($filters['verification_result'] == 'unverified') {
                        $query = $query->whereNull('email_verifications.result');
                    } else {
                        $query = $query->where('email_verifications.result', '=', $filters['verification_result']);
                    }
                }
            }

            // outside filters
            if (!empty($request->status)) {
                $query = $query->where('subscribers.status', '=', $request->status);
            }
            if (!empty($request->verification_result)) {
                $query = $query->where('email_verifications.result', '=', $request->verification_result);
            }

            // Open
            if ($request->open == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('open_logs')
                        ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Not Open
            if ($request->open == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('open_logs')
                        ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Click
            if ($request->click == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('click_logs')
                        ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }

            // Not Click
            if ($request->click == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('click_logs')
                        ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
                        ->whereRaw(table('tracking_logs') . '.subscriber_id = ' . table('subscribers') . '.id');
                });
            }
        }

        return $query;
    }

    /**
     * Get all languages.
     *
     * @return collect
     */
    public static function scopeSearch($query, $request, $customer = null)
    {
        $query = self::select('subscribers.*', 'email_verifications.result as verify_result');

        // Filter by customer
        if (!isset($customer)) {
            $customer = $request->selected_customer;
        }
        $query = $query->where('mail_lists.customer_id', '=', $customer->id);

        // Filter
        $query = $query->filter($request);

        // Order
        if (isset($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    public function isSubscribed()
    {
        return $this->status == self::STATUS_SUBSCRIBED;
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
        $week = 'week';
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

        $results = [];
        $mail_list_ids = MailList::where('customer_id', $customer->id)->pluck('id')->toArray();
        if (count($mail_list_ids))
            $results = Subscriber::selectRaw("date_format(created_at, '${mysql_format}') formatted_date, date(min(created_at)) date, count(*) number_of_subscribers")
                ->whereIn('mail_list_id', $mail_list_ids)
                ->where('created_at', '<=', $end_date)
                ->where('created_at', '>=', $start_date)
                ->groupBy('formatted_date')->get();

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
            if (empty($date_wise_results[$formatted_date])) {

                $date_wise_results[$formatted_date] = [
                    'formatted_date' => $formatted_date,
                    'date' => $result_date->format("Y-m-d"),
                    'number_of_subscribers' => 0,
                ];
            }
            $result_date = $result_date->addDay();
        } while ($result_date < $end_date);

        usort($date_wise_results, function ($a, $b) {
            return ($a['formatted_date'] ?? '') <=> ($b['formatted_date'] ?? '');
        });

        $total_list = 0;
        $total_subscribers = 0;
        foreach ($date_wise_results as $result) {
            $total_subscribers += $result['number_of_subscribers'] ?? 0;
        }

        return [
            'total_subscriber' => $total_subscribers,
            'items' => array_values($date_wise_results)
        ];

    }

    static function createSubscriber($list, $email, $first_name, $last_name, $timestamp = null)
    {
        if (!$list) return null;
        /** @var self $subscriber */
        $subscriber = $list->subscribers()->where('email', $email)->first();
        if ($subscriber != null) return $subscriber;
        if (!$timestamp) $timestamp = Carbon::now();


        /** @var MailList $list */
        DB::transaction(function () use ($list, $email, $first_name, $last_name, $timestamp, &$subscriber) {
            $email_field = $list->getFieldByTag('EMAIL');
            $first_name_field = $list->getFieldByTag('FIRST_NAME');
            $last_name_field = $list->getFieldByTag('LAST_NAME');

            $subscriber = new Subscriber();
            $subscriber->mail_list_id = $list->id;
            $subscriber->status = 'subscribed';
            $subscriber->email = $email;
            $subscriber->created_at = $timestamp;
            $subscriber->save();

            if ($email_field) {
                $fv = new SubscriberField();
                $fv->subscriber_id = $subscriber->id;
                $fv->field_id = $email_field->id;
                $fv->value = $email;
                $fv->created_at = $timestamp;
                $fv->save();
            }

            if ($first_name_field) {
                $fv = new SubscriberField();
                $fv->subscriber_id = $subscriber->id;
                $fv->field_id = $first_name_field->id;
                $fv->value = $first_name;
                $fv->created_at = $timestamp;
                $fv->save();
            }

            if ($last_name_field) {
                $fv = new SubscriberField();
                $fv->subscriber_id = $subscriber->id;
                $fv->field_id = $last_name_field->id;
                $fv->value = $last_name;
                $fv->created_at = $timestamp;
                $fv->save();
            }
        });

        /** @var MailList $list */
        DB::transaction(function () use ($list) {
            $segments = $list->customer->segment2s;
            foreach ($segments as $segment) {
                $segment->sync_needed = true;
                $segment->save();
            }
        });
        return $subscriber;
    }
}
