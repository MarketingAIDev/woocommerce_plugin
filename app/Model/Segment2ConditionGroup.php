<?php

namespace Acelle\Model;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionOrGroup
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer segment2_id
 * @property Segment2 segment2
 *
 * @property Segment2Condition[] conditions
 */
class Segment2ConditionGroup extends Model
{
    public function applyFilter($query)
    {
        foreach ($this->conditions as $condition) {
            $query->orWhere(Closure::fromCallable(array($condition, 'applyFilter')));
        }
        return $query;
    }

    public function segment2()
    {
        return $this->belongsTo(Segment2::class, 'segment2_id');
    }

    public function conditions()
    {
        return $this->hasMany(Segment2Condition::class, 'group_id');
    }

    public static function store_group(Segment2 $segment2, $data, $prefix = '')
    {
        $group = custom_validate($data, [
            'conditions' => 'required|array|min:1'
        ], $prefix);

        $model = new self();
        $model->segment2_id = $segment2->id;
        $model->save();

        $conditions = $group['conditions'] ?? [];
        foreach ($conditions as $key => $condition) {
            Segment2Condition::store_condition($model, $condition ?? [], $prefix."conditions.$key.");
        }

        return $model;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $item) {
            foreach ($item->conditions as $condition) {
                $condition->delete();
            }
        });
    }
}
