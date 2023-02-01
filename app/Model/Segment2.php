<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Segment2
 * @package Acelle
 *
 * @property string uid
 * @property integer id
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property string name
 * @property string customer_id
 * @property Customer customer
 * @property string|Carbon|null synced_at
 * @property bool sync_needed
 * @property bool is_syncing
 *
 * @property Segment2ConditionGroup[] groups
 * @property Subscriber[] subscribers
 */
class Segment2 extends Model
{
    const COLUMN_uid = "uid";
    const COLUMN_id = "id";
    const COLUMN_created_at = "created_at";
    const COLUMN_updated_at = "updated_at";
    const COLUMN_name = "name";
    const COLUMN_customer_id = "customer_id";
    const COLUMN_synced_at = "synced_at";
    const COLUMN_sync_needed = "sync_needed";
    const COLUMN_is_syncing = "is_syncing";

    protected $dates = [
        self::COLUMN_synced_at
    ];

    protected $casts = [
        self::COLUMN_sync_needed => 'bool',
        self::COLUMN_is_syncing => 'bool',
    ];

    public function applyFilterGroups($query)
    {
        foreach ($this->groups as $group) {
            $query->where(Closure::fromCallable(array($group, 'applyFilter')));
        }
    }

    static function syncAll()
    {
        $sync_needed_segments = self::query()
            ->where(self::COLUMN_sync_needed, true)
            ->where(self::COLUMN_is_syncing, false)
            ->get();
        foreach ($sync_needed_segments as $segment) {
            /** @var Segment2 $segment */
            $segment->syncSubscribers();
        }

        $an_hour_ago = Carbon::now()->subHour();
        $old_segments = self::query()
            ->whereNull(self::COLUMN_synced_at)
            ->orWhere(self::COLUMN_synced_at, '<', $an_hour_ago)
            ->where(self::COLUMN_is_syncing, false)
            ->limit(10)
            ->get();

        foreach ($old_segments as $segment) {
            /** @var Segment2 $segment */
            $segment->syncSubscribers();
        }
    }

    public function syncSubscribers()
    {
        print("Syncing: " . $this->uid);
        $this->is_syncing = true;
        $this->save();

        $subscriber_ids = [];
        $list = $this->customer ? $this->customer->getDefaultMailingList() : null;
        if ($list instanceof MailList) {
            $query = Subscriber::query();
            $query->where('mail_list_id', $list->id);
            $this->applyFilterGroups($query);
            $subscriber_ids = $query->pluck('id')->toArray();
        }
        $this->subscribers()->sync($subscriber_ids);
        $this->is_syncing = false;
        $this->sync_needed = false;
        $this->synced_at = Carbon::now();
        $this->save();
        print("Synced: " . $this->uid);
    }

    /**
     * Find item by uid.
     *
     * @return self|null
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /*public function filter($query)
    {
        $query->where('filter', '>', '1');
    }*/

    public function scopeWithAllConditions(Builder $query)
    {
        return $query->with('groups')
            ->with('groups.conditions')
            ->with('groups.conditions.action')
            ->with('groups.conditions.location')
            ->with('groups.conditions.property');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Segment2ConditionGroup::class, 'segment2_id');
    }

    function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class);
    }

    public static function store_segment2_rules()
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }

    public static function store_segment2(Customer $customer, $data)
    {
        $model = new self();
        $model->customer_id = $customer->id;
        $model->name = $data['name'];
        $model->sync_needed = true;
        $model->save();

        // Create a group by default
        $group = Segment2ConditionGroup::store_group($model, [
            'conditions' => [
                'condition_type' => null
            ]
        ]);

        return $model;
    }

    public function update_segment2($data)
    {
        $segment_data = custom_validate($data, [
            'name' => 'required|string|max:255',
            'groups' => 'required|array|min:1'
        ]);

        $this->name = $segment_data['name'] ?? "";
        $this->sync_needed = true;
        $this->save();

        // Delete existing groups
        foreach ($this->groups as $group) {
            $group->delete();
        }

        $groups = $segment_data['groups'] ?? [];
        foreach ($groups as $key => $group) {
            Segment2ConditionGroup::store_group($this, $group, "groups.$key.");
        }
    }

    public static function validate_and_store(Customer $customer, $data, $error_prefix = '')
    {
        $data = custom_validate($data, [
            'name' => 'required|string|max:255',
        ], $error_prefix);

        $model = new self();
        $model->customer_id = $customer->id;
        $model->name = $data['name'];
        $model->save();

        return $model;
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
        });
    }
}
