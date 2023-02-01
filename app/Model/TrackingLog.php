<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class TrackingLog
 * @package Acelle\Model
 * @property integer id
 * @property string runtime_message_id
 * @property integer message_id
 * @property integer customer_id
 * @property integer sending_server_id
 * @property integer campaign_id
 * @property integer subscriber_id
 * @property string status
 * @property string error
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property integer auto_trigger_id
 * @property integer sub_account_id
 * @property integer email_id
 *
 * @property Campaign campaign
 * @property Subscriber subscriber
 * @property SendingServer sendingServer
 */
class TrackingLog extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_runtime_message_id = 'runtime_message_id';
    const COLUMN_message_id = 'message_id';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_sending_server_id = 'sending_server_id';
    const COLUMN_campaign_id = 'campaign_id';
    const COLUMN_subscriber_id = 'subscriber_id';
    const COLUMN_status = 'status';
    const COLUMN_error = 'error';
    const COLUMN_auto_trigger_id = 'auto_trigger_id';
    const COLUMN_sub_account_id = 'sub_account_id';
    const COLUMN_email_id = 'email_id';

    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_FEEDBACK_ABUSE = 'feedback-abuse';
    const STATUS_FEEDBACK_SPAM = 'feedback-spam';

    protected $fillable = [
        'email_id',
        'campaign_id',
        'message_id',
        'runtime_message_id',
        'subscriber_id',
        'sending_server_id',
        'customer_id',
        'status',
        'error',
        'auto_trigger_id',
        'sub_account_id',
        'mail_list_id'
    ];

    protected $hidden = [
        'campaign',
        'subscriber',
        'sendingServer'
    ];

    protected $appends = [
        'campaign_name',
        'subscriber_name',
        'subscriber_email',
        'sending_server_name'
    ];

    public function getCampaignNameAttribute()
    {
        return $this->campaign ? $this->campaign->name : '[deleted]';
    }

    public function getSubscriberNameAndEmailAttribute()
    {
        $s = $this->subscriber;
        if (!$s) return '[deleted';
        $name = $s->getFullName();
        $email = $s->email;

        return "$name ($email)";
    }

    public function getSubscriberNameAttribute()
    {
        return $this->subscriber ? $this->subscriber->getFullName() : '[deleted]';
    }

    public function getSubscriberEmailAttribute()
    {
        return $this->subscriber ? $this->subscriber->email : '[deleted]';
    }

    public function getSendingServerNameAttribute()
    {
        return $this->sendingServer ? $this->sendingServer->name : '[deleted]';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function mailList(): BelongsTo
    {
        return $this->belongsTo(MailList::class);
    }

    public function sendingServer(): BelongsTo
    {
        return $this->belongsTo(SendingServer::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public static function getAll()
    {
        return self::select('tracking_logs.*');
    }

    public static function filter($request)
    {
        $user = $request->user();
        $customer = $request->selected_customer;
        $query = self::select('tracking_logs.*');
        $query = $query->leftJoin('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id');
        $query = $query->leftJoin('campaigns', 'campaigns.id', '=', 'tracking_logs.campaign_id');
        $query = $query->leftJoin('sending_servers', 'sending_servers.id', '=', 'tracking_logs.sending_server_id');
        $query = $query->leftJoin('customers', 'customers.id', '=', 'tracking_logs.customer_id');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('campaigns.name', 'like', '%' . $keyword . '%')
                        ->orwhere('tracking_logs.status', 'like', '%' . $keyword . '%')
                        ->orwhere('sending_servers.name', 'like', '%' . $keyword . '%')
                        ->orwhere(\DB::raw('CONCAT(first_name, last_name)'), 'like', '%' . $keyword . '%')
                        ->orwhere('subscribers.email', 'like', '%' . $keyword . '%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['campaign_uid'])) {
                $query = $query->where('campaigns.uid', '=', $filters['campaign_uid']);
            }
        }

        return $query;
    }

    public static function search($request, $campaign = null)
    {
        $query = self::filter($request);

        if (isset($campaign)) {
            $query = $query->where('tracking_logs.campaign_id', '=', $campaign->id);
        }

        if ($request->sort_order == 'uid')
            $request->sort_order = (new Subscriber)->getTable() . '.uid';

        if ($request->sort_order && $request->sort_direction)
            $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    static function getEmailsReport(Request $request, Customer $customer): array
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
            'total_emails' => self::getTotalCount($customer, $start_date, $end_date),
            'delivered_email' => self::getTotalCount($customer, $start_date, $end_date, "delivered"),
            'opened_email' => self::getTotalCount($customer, $start_date, $end_date, "opened"),
            'clicked_email' => self::getTotalCount($customer, $start_date, $end_date, "clicked"),
        ];
    }

    static function getTotalCount($customer, $start_date, $end_date, $filter = null): int
    {
        $query = self::query()->where(self::COLUMN_customer_id, $customer->id);
        switch ($filter) {
            case "clicked":
                $query->whereHas('click_logs');
                break;
            case "opened":
                $query->whereHas('open_logs');
                break;
            case "delivered":
                $query->whereDoesntHave('bounce_logs');
                break;
        }

        if ($end_date)
            $query->where(self::COLUMN_created_at, '<=', $end_date);
        if ($start_date)
            $query->where(self::COLUMN_created_at, '>=', $start_date);

        return $query->count();
    }

    function click_logs(): HasMany
    {
        return $this->hasMany(ClickLog::class, 'message_id', 'message_id');
    }

    function open_logs(): HasMany
    {
        return $this->hasMany(OpenLog::class, 'message_id', 'message_id');
    }

    function bounce_logs(): HasMany
    {
        return $this->hasMany(BounceLog::class, 'message_id', 'message_id');
    }

    function feedback_logs(): HasMany
    {
        return $this->hasMany(FeedbackLog::class, 'message_id', 'message_id');
    }

    function unsubscribe_logs(): HasMany
    {
        return $this->hasMany(UnsubscribeLog::class, 'message_id', 'message_id');
    }

    public static $itemsPerPage = 25;
}
