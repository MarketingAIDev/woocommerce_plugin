<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionLocation
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer condition_id
 * @property Segment2Condition condition
 *
 * @property integer location_type_id
 * @property Segment2ConditionLocationType location_type
 */
class Segment2ConditionLocation extends Model
{
    public const COLUMN_id = 'id';
    public const COLUMN_condition_id = 'condition_id';
    public const COLUMN_location_type_id = 'location_type_id';

    protected $appends = [
        'condition_type'
    ];

    public function getConditionTypeAttribute()
    {
        return 'location';
    }

    /**
     * @param Builder $original_query
     */
    public function applyFilter($original_query)
    {
        switch ($this->location_type->code) {
            case Segment2ConditionLocationType::CODE_INSIDE_EU:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->whereIn(ShopifyCustomer::COLUMN_country_code, Segment2ConditionLocationType::COUNTRY_CODES_IN_EU);
                });
                break;
            case Segment2ConditionLocationType::CODE_OUTSIDE_EU:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->whereNotIn(ShopifyCustomer::COLUMN_country_code, Segment2ConditionLocationType::COUNTRY_CODES_IN_EU);
                });
                break;
            case Segment2ConditionLocationType::CODE_INSIDE_US:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->where(ShopifyCustomer::COLUMN_country_code, Segment2ConditionLocationType::COUNTRY_CODE_US);
                });
                break;
            case Segment2ConditionLocationType::CODE_OUTSIDE_US:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->where(ShopifyCustomer::COLUMN_country_code, "!=", Segment2ConditionLocationType::COUNTRY_CODE_US);
                });
                break;
        }
    }

    public static function rules($data)
    {
        return [
            self::COLUMN_location_type_id => 'required|integer|exists:segment2_condition_location_types,id',
        ];
    }


    public static function store_condition(Segment2Condition $condition, $data, $prefix = '')
    {
        $data = custom_validate($data, self::rules($data), $prefix);
        $model = new self();
        $model->condition_id = $condition->id;
        $model->location_type_id = $data[self::COLUMN_location_type_id] ?? 0;
        $model->save();
        return $model;
    }

    public function update_condition($data)
    {
        $this->location_type_id = $data[self::COLUMN_location_type_id] ?? 0;
        $this->save();
        return $this;
    }

    // region Eloquent Relationships
    public function condition()
    {
        return $this->belongsTo(Segment2Condition::class, 'condition_id');
    }

    public function location_type()
    {
        return $this->belongsTo(Segment2ConditionLocationType::class, 'location_type_id');
    }
    // endregion
}
