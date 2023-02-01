<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\Rule;

/**
 * Class Segment2Condition
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer group_id
 * @property Segment2ConditionGroup group
 * @property string condition_type
 *
 * @property Segment2ConditionAction action
 * @property Segment2ConditionProperty property
 * @property Segment2ConditionLocation location
 */
class Segment2Condition extends Model
{
    public const CONDITION_TYPE_ACTION = 'action';
    public const CONDITION_TYPE_PROPERTY = 'property';
    public const CONDITION_TYPE_LOCATION = 'location';
    public const CONDITION_TYPES = [
        self::CONDITION_TYPE_ACTION,
        self::CONDITION_TYPE_PROPERTY,
        self::CONDITION_TYPE_LOCATION,
    ];

    public function getConditionAttribute()
    {
        switch ($this->condition_type) {
            case self::CONDITION_TYPE_ACTION:
                return $this->action;
            case self::CONDITION_TYPE_PROPERTY:
                return $this->property;
            case self::CONDITION_TYPE_LOCATION:
                return $this->location;
        }
        return null;
    }

    public static function store_condition(Segment2ConditionGroup $group, $original_data, $prefix = '')
    {
        $data = custom_validate($original_data, [
            'condition_type' => ['nullable', 'string', Rule::in([
                self::CONDITION_TYPE_ACTION,
                self::CONDITION_TYPE_PROPERTY,
                self::CONDITION_TYPE_LOCATION,
            ])],
            'action' => 'nullable',
            'property' => 'nullable',
            'location' => 'nullable'
        ], $prefix);

        $model = new self();
        $model->group_id = $group->id;
        $model->condition_type = $data['condition_type'] ?? '';
        if (!$model->save())
            return false;

        switch ($model->condition_type) {
            case self::CONDITION_TYPE_ACTION:
                $action_data = $data['action'] ?? [];
                Segment2ConditionAction::store_condition($model, $action_data, $prefix . 'action.');
                break;
            case self::CONDITION_TYPE_PROPERTY:
                $property_data = $data['property'] ?? [];
                Segment2ConditionProperty::store_condition($model, $property_data, $prefix . 'property.');
                break;
            case self::CONDITION_TYPE_LOCATION:
                $property_data = $data['location'] ?? [];
                Segment2ConditionLocation::store_condition($model, $property_data, $prefix . 'location.');
                break;
        }

        return $model;
    }

    public function applyFilter($query)
    {
        switch ($this->condition_type) {
            case self::CONDITION_TYPE_ACTION:
                $this->action->applyFilter($query);
                break;
            case self::CONDITION_TYPE_PROPERTY:
                $this->property->applyFilter($query);
                break;
            case self::CONDITION_TYPE_LOCATION:
                $this->location->applyFilter($query);
                break;
        }
        return $query;
    }

    // region Eloquent Relationships
    public function group(): BelongsTo
    {
        return $this->belongsTo(Segment2ConditionGroup::class, 'group_id');
    }

    public function action(): HasOne
    {
        return $this->hasOne(Segment2ConditionAction::class, 'condition_id');
    }

    public function property(): HasOne
    {
        return $this->hasOne(Segment2ConditionProperty::class, 'condition_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(Segment2ConditionLocation::class, 'condition_id');
    }

    // endregion

    protected static function boot()
    {
        parent::boot();
        static::deleting(function (self $item) {
            $item->action()->delete();
            $item->property()->delete();
            $item->location()->delete();
        });
    }
}
