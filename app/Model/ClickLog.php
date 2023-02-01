<?php

/**
 * ClickLog class.
 *
 * Model class for click logs
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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property TrackingLog trackingLog
 */
class ClickLog extends Model
{
    const SORTABLE_FIELDS = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'subscriber_name',
    ];

    public function getSubscriberNameAttribute()
    {
        if(!$this->trackingLog) return '[deleted]';
        return $this->trackingLog->getSubscriberNameAndEmailAttribute();
    }

    public function trackingLog(): BelongsTo
    {
        return $this->belongsTo(TrackingLog::class, 'message_id', 'message_id');
    }

    public static function getAll()
    {
        return self::select('click_logs.*');
    }

    public static function filter($request)
    {
        $user = $request->user();
        $customer = $request->selected_customer;
        $query = self::select('click_logs.*');
        $query = $query->leftJoin('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id');
        $query = $query->leftJoin('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id');
        $query = $query->leftJoin('campaigns', 'campaigns.id', '=', 'tracking_logs.campaign_id');
        $query = $query->leftJoin('sending_servers', 'sending_servers.id', '=', 'tracking_logs.sending_server_id');
        $query = $query->leftJoin('customers', 'customers.id', '=', 'tracking_logs.customer_id');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('campaigns.name', 'like', '%' . $keyword . '%')
                        ->orwhere('click_logs.ip_address', 'like', '%' . $keyword . '%')
                        ->orwhere('click_logs.url', 'like', '%' . $keyword . '%')
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

        if ($request->sort_order && in_array($request->sort_order, self::SORTABLE_FIELDS))
            $query = $query->orderBy("click_logs." . $request->sort_order, $request->sort_direction == "asc" ? "asc" : "desc");

        return $query;
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;
}
