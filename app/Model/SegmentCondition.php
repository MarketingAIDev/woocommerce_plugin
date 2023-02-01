<?php

/**
 * SegmentCondition class.
 *
 * Model class for segment filter options
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

/**
 * @property int field_id
 * @property Field field
 * @property string operator
 * @property string value
 * @property string special_condition
 * @property string time_period
 * @property string time_period_in_last_days
 * @property int segment_id
 * @property Segment segment
 */
class SegmentCondition extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_id', 'operator', 'value',
    ];

    public function field()
    {
        return $this->belongsTo('Acelle\Model\Field');
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
            while (SegmentCondition::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }
}
