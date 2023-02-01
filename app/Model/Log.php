<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Log
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer customer_id
 * @property Customer customer
 * @property string type
 * @property string name
 * @property string data
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 */
class Log extends Model
{
    public static $itemsPerPage = 25;

    protected $fillable = [
        'customer_id', 'type', 'name', 'data',
    ];

    protected $appends = ['extra_data'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getExtraDataAttribute()
    {
        $data = (array)json_decode($this->data);
        if (isset($data['name'])) {
            $data['name'] = strip_tags($data['name']);
        }

        // Campaign
        if ($this->type == 'campaign') {
            if (isset($data['list_id'])) {
                $list = MailList::find($data['list_id']);
                $list_link = is_object($list) ? action('MailListController@show', $list->uid) : '#deleted';
                $data['list_link'] = $list_link;
                $data['list_uid'] = is_object($list) ? $list->uid : null;
            }

            $item = Campaign::find($data['id'] ?? null);
            $link = is_object($item) ? action('CampaignController@overview', $item->uid) : '#deleted';

            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Subscriber
        elseif ($this->type == 'subscriber') {
            $list = MailList::find($data['list_id']);
            $list_link = is_object($list) ? action('MailListController@overview', $list->uid) : '#deleted';
            $data['list_link'] = $list_link;
            $data['list_uid'] = is_object($list) ? $list->uid : null;

            $item = Subscriber::find($data['id'] ?? null);
            $link = is_object($item) && is_object($list) ? action('SubscriberController@edit', ['uid' => $item->uid, 'list_uid' => $list->uid]) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Segment
        elseif ($this->type == 'segment') {
            $list = MailList::find($data['list_id']);
            $list_link = is_object($list) ? action('MailListController@overview', $list->uid) : '#deleted';
            $data['list_link'] = $list_link;
            $data['list_uid'] = is_object($list) ? $list->uid : null;

            $item = Segment::find($data['id'] ?? null);
            $link = is_object($item) && is_object($list) ? action('SegmentController@edit', ['uid' => $item->uid, 'list_uid' => $list->uid]) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Page
        elseif ($this->type == 'page') {
            $list = MailList::find($data['list_id']);
            $list_link = is_object($list) ? action('MailListController@overview', $list->uid) : '#deleted';
            $data['list_link'] = $list_link;
            $data['list_uid'] = is_object($list) ? $list->uid : null;

            $item = Page::find($data['id'] ?? null);
            $link = is_object($item) && is_object($list) ? action('PageController@update', ['list_uid' => $list->uid, 'alias' => $data['alias']]) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
            $data['name'] = trans('messages.' . $data['alias']);
        } // List
        elseif ($this->type == 'list') {
            $item = MailList::find($data['id'] ?? null);
            $link = is_object($item) ? action('MailListController@overview', $item->uid) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;

            if (isset($data['from_uid'])) {
                $from = MailList::findByUid($data['from_uid']);
                $from_link = is_object($from) ? action('MailListController@overview', $data['from_uid']) : '#deleted';
                $data['from_link'] = $from_link;
                $data['from_uid'] = is_object($from) ? $from->uid : null;
            }
            if (isset($data['to_uid'])) {
                $to = MailList::findByUid($data['to_uid']);
                $to_link = is_object($to) ? action('MailListController@overview', $data['to_uid']) : '#deleted';
                $data['to_link'] = $to_link;
                $data['to_uid'] = is_object($to) ? $to->uid : null;
            }
        } // Campaign
        elseif ($this->type == 'automation') {
            if (isset($data['list_id'])) {
                $list = MailList::find($data['list_id']);
                $list_link = is_object($list) ? action('MailListController@show', $list->uid) : '#deleted';
                $data['list_link'] = $list_link;
                $data['list_uid'] = is_object($list) ? $list->uid : null;
            }

            $item = Automation2::find($data['id'] ?? null);
            $link = is_object($item) ? action('Automation2Controller@edit', $item->uid) : '#deleted';

            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Sending server
        elseif ($this->type == 'sending_server') {
            $item = SendingServer::find($data['id'] ?? null);
            $link = is_object($item) ? action('SendingServerController@edit', ['id' => $item->uid, 'type' => $item->type]) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Sending server
        elseif ($this->type == 'sending_domain') {
            $item = SendingDomain::find($data['id'] ?? null);
            $link = is_object($item) ? action('SendingDomainController@edit', $item->uid) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        } // Email verification server
        elseif ($this->type == 'email_verification_server') {
            $item = EmailVerificationServer::find($data['id'] ?? null);
            $link = is_object($item) ? action('EmailVerificationServerController@edit', $item->uid) : '#deleted';
            $data['link'] = $link;
            $data['uid'] = is_object($item) ? $item->uid : null;
        }
        return $data;
    }

    public function message()
    {
        return trans('messages.log.' . $this->type . '.' . $this->name, $this->getExtraDataAttribute());
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $customer = $request->selected_customer;
        $query = self::where('customer_id', '=', $customer->id);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['type'])) {
                $query = $query->where('logs.type', '=', $filters['type']);
            }
        }

        return $query;
    }

    public static function search($request)
    {
        $query = self::filter($request);

        if(!empty($request->sort_order) && !empty($request->sort_direction)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction === "asc" ? "asc" : "desc");
        }

        return $query;
    }
}
