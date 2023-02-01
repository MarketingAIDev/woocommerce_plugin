<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionProperty
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer condition_id
 * @property Segment2Condition condition
 *
 * @property integer property_type_id
 * @property string property_data_type
 * @property Segment2ConditionPropertyType property_type
 * @property string comparison_type
 * @property string comparison_value_date
 * @property string comparison_value_text
 * @property string comparison_value_number
 *
 */
class Segment2ConditionProperty extends Model
{
    public const COLUMN_id = 'id';
    public const COLUMN_condition_id = 'condition_id';
    public const COLUMN_property_type_id = 'property_type_id';
    public const COLUMN_property_data_type = 'property_data_type';
    public const COLUMN_comparison_type = 'comparison_type';
    public const COLUMN_comparison_value___DEPRECATED = 'comparison_value';
    public const COLUMN_comparison_value_date = 'comparison_value_date';
    public const COLUMN_comparison_value_text = 'comparison_value_text';
    public const COLUMN_comparison_value_number = 'comparison_value_number';

    // region Constants
    public const TEXT_COMPARISON_TYPE_EQUAL = 'text__equal';
    public const TEXT_COMPARISON_TYPE_NOT_EQUAL = 'text__not_equal';
    public const TEXT_COMPARISON_TYPE_CONTAINS = 'text__contains';
    public const TEXT_COMPARISON_TYPE_NOT_CONTAINS = 'text__not_contains';
    public const TEXT_COMPARISON_TYPE_SET = 'text__set';
    public const TEXT_COMPARISON_TYPE_NOT_SET = 'text__not_set';
    public const TEXT_COMPARISON_TYPE_STARTS = 'text__starts';
    public const TEXT_COMPARISON_TYPE_NOT_STARTS = 'text__not_starts';
    public const TEXT_COMPARISON_TYPE_ENDS = 'text__ends';
    public const TEXT_COMPARISON_TYPE_NOT_ENDS = 'text__not_ends';
    public const TEXT_COMPARISON_TYPES = [
        self::TEXT_COMPARISON_TYPE_EQUAL,
        self::TEXT_COMPARISON_TYPE_NOT_EQUAL,
        self::TEXT_COMPARISON_TYPE_CONTAINS,
        self::TEXT_COMPARISON_TYPE_NOT_CONTAINS,
        self::TEXT_COMPARISON_TYPE_SET,
        self::TEXT_COMPARISON_TYPE_NOT_SET,
        self::TEXT_COMPARISON_TYPE_STARTS,
        self::TEXT_COMPARISON_TYPE_NOT_STARTS,
        self::TEXT_COMPARISON_TYPE_ENDS,
        self::TEXT_COMPARISON_TYPE_NOT_ENDS,
    ];

    public const DATE_COMPARISON_TYPE_EQUAL = 'date__equal';
    public const DATE_COMPARISON_TYPE_NOT_EQUAL = 'date__not_equal';
    public const DATE_COMPARISON_TYPE_BEFORE = 'date__before';
    public const DATE_COMPARISON_TYPE_AFTER = 'date__after';
    public const DATE_COMPARISON_TYPES = [
        self::DATE_COMPARISON_TYPE_EQUAL,
        self::DATE_COMPARISON_TYPE_NOT_EQUAL,
        self::DATE_COMPARISON_TYPE_BEFORE,
        self::DATE_COMPARISON_TYPE_AFTER,
    ];

    public const NUMBER_COMPARISON_TYPE_EQUAL = 'number__equal';
    public const NUMBER_COMPARISON_TYPE_NOT_EQUAL = 'number__not_equal';
    public const NUMBER_COMPARISON_TYPE_GREATER_THAN = 'number__greater_than';
    public const NUMBER_COMPARISON_TYPE_LESS_THAN = 'number__less_than';
    public const NUMBER_COMPARISON_TYPES = [
        self::NUMBER_COMPARISON_TYPE_EQUAL,
        self::NUMBER_COMPARISON_TYPE_NOT_EQUAL,
        self::NUMBER_COMPARISON_TYPE_GREATER_THAN,
        self::NUMBER_COMPARISON_TYPE_LESS_THAN,
    ];

    public const BOOLEAN_COMPARISON_TYPE_TRUE = 'boolean__true';
    public const BOOLEAN_COMPARISON_TYPE_FALSE = 'boolean__false';
    public const BOOLEAN_COMPARISON_TYPES = [
        self::BOOLEAN_COMPARISON_TYPE_TRUE,
        self::BOOLEAN_COMPARISON_TYPE_FALSE
    ];
    // endregion

    protected $appends = [
        'condition_type'
    ];

    protected $casts = [
        self::COLUMN_comparison_value_date => 'date:Y-m-d'
    ];

    public function getConditionTypeAttribute()
    {
        return 'property';
    }


    /**
     * @param Builder $original_query
     */
    public function applyFilter($original_query)
    {
        switch ($this->property_type->code) {
            case Segment2ConditionPropertyType::CODE_ACCEPTS_MARKETING:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyBooleanFilter($customer_query, ShopifyCustomer::COLUMN_accepts_marketing);
                });
                break;
            case Segment2ConditionPropertyType::CODE_ACCEPTS_MARKETING_UPDATE_DATE:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyDateFilter($customer_query, ShopifyCustomer::COLUMN_accepts_marketing_updated_at);
                });
                break;
            case Segment2ConditionPropertyType::CODE_CURRENCY:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_currency);
                });
                break;
            case Segment2ConditionPropertyType::CODE_JOINING_DATE:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyDateFilter($customer_query, ShopifyCustomer::COLUMN_shopify_created_at);
                });
                break;
            case Segment2ConditionPropertyType::CODE_EMAIL_ADDRESS:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_email);
                });
                break;
            case Segment2ConditionPropertyType::CODE_FIRST_NAME:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_first_name);
                });
                break;
            case Segment2ConditionPropertyType::CODE_LAST_NAME:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_last_name);
                });
                break;
            case Segment2ConditionPropertyType::CODE_NOTE:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_note);
                });
                break;
            case Segment2ConditionPropertyType::CODE_NUMBER_OF_ORDERS:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyNumberFilter($customer_query, ShopifyCustomer::COLUMN_orders_count);
                });
                break;
            case Segment2ConditionPropertyType::CODE_PHONE:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_phone_number);
                });
                break;
            case Segment2ConditionPropertyType::CODE_ACCOUNT_STATE:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyTextFilter($customer_query, ShopifyCustomer::COLUMN_state);
                });
                break;
            case Segment2ConditionPropertyType::CODE_TOTAL_SPENT:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyNumberFilter($customer_query, ShopifyCustomer::COLUMN_total_spent);
                });
                break;
            case Segment2ConditionPropertyType::CODE_VERIFIED_EMAIL:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $this->applyBooleanFilter($customer_query, ShopifyCustomer::COLUMN_verified_email);
                });
                break;
        }
    }

    /**
     * @param Builder $query
     * @param string $column
     */
    function applyBooleanFilter($query, $column)
    {
        $operator = ($this->comparison_type == self::BOOLEAN_COMPARISON_TYPE_TRUE) ? "=" : "!-";
        $query->where($column, $operator, 1);
    }

    /**
     * @param Builder $query
     * @param string $column
     */
    function applyDateFilter($query, $column)
    {
        $value = Carbon::parse($this->comparison_value_date);
        switch ($this->comparison_type) {
            case self::DATE_COMPARISON_TYPE_EQUAL:
                $query->where($column, '=', $value);
                break;
            case self::DATE_COMPARISON_TYPE_NOT_EQUAL:
                $query->where($column, '!=', $value);
                break;
            case self::DATE_COMPARISON_TYPE_BEFORE:
                $query->where($column, '<', $value);
                break;
            case self::DATE_COMPARISON_TYPE_AFTER:
                $query->where($column, '>', $value);
                break;
        }
    }

    /**
     * @param Builder $query
     * @param string $column
     */
    function applyNumberFilter($query, $column)
    {
        $value = $this->comparison_value_number;
        switch ($this->comparison_type) {
            case self::NUMBER_COMPARISON_TYPE_EQUAL:
                $query->where($column, '=', $value);
                break;
            case self::NUMBER_COMPARISON_TYPE_NOT_EQUAL:
                $query->where($column, '!=', $value);
                break;
            case self::NUMBER_COMPARISON_TYPE_GREATER_THAN:
                $query->where($column, '<', $value);
                break;
            case self::NUMBER_COMPARISON_TYPE_LESS_THAN:
                $query->where($column, '>', $value);
                break;
        }
    }

    /**
     * @param Builder $query
     * @param string $column
     */
    function applyTextFilter($query, $column)
    {
        $value = $this->comparison_value_text;
        switch ($this->comparison_type) {
            case self::TEXT_COMPARISON_TYPE_EQUAL:
                $query->where($column, '=', $value);
                break;
            case self::TEXT_COMPARISON_TYPE_NOT_EQUAL:
                $query->where($column, '!=', $value);
                break;
            case self::TEXT_COMPARISON_TYPE_CONTAINS:
                $query->where($column, 'like', "%$value%");
                break;
            case self::TEXT_COMPARISON_TYPE_NOT_CONTAINS:
                $query->where($column, 'not like', "%$value%");
                break;
            case self::TEXT_COMPARISON_TYPE_SET:
                $values = explode(',', $value ?? "", 20);
                $query->whereIn($column, $values);
                break;
            case self::TEXT_COMPARISON_TYPE_NOT_SET:
                $values = explode(',', $value ?? "", 20);
                $query->whereNotIn($column, $values);
                break;
            case self::TEXT_COMPARISON_TYPE_STARTS:
                $query->where($column, 'like', "$value%");
                break;
            case self::TEXT_COMPARISON_TYPE_NOT_STARTS:
                $query->where($column, 'not like', "$value%");
                break;
            case self::TEXT_COMPARISON_TYPE_ENDS:
                $query->where($column, 'like', "%$value");
                break;
            case self::TEXT_COMPARISON_TYPE_NOT_ENDS:
                $query->where($column, 'not like', "%$value");
                break;
        }
    }


    public static function rules($data)
    {
        $rules = [
            self::COLUMN_property_type_id => 'required|integer|exists:segment2_condition_property_types,id',
        ];

        /** @var Segment2ConditionPropertyType $selected_property_type */
        $selected_property_type = Segment2ConditionPropertyType::find($data[self::COLUMN_property_type_id] ?? 0);
        if ($selected_property_type) {
            switch ($selected_property_type->data_type) {
                case Segment2ConditionPropertyType::DATA_TYPE_TEXT:
                    $rules[self::COLUMN_comparison_type] = 'required|string|in:' . implode(',', self::TEXT_COMPARISON_TYPES);
                    $rules[self::COLUMN_comparison_value_text] = 'required|string';
                    break;
                case Segment2ConditionPropertyType::DATA_TYPE_DATE:
                    $rules[self::COLUMN_comparison_type] = 'required|string|in:' . implode(',', self::DATE_COMPARISON_TYPES);
                    $rules[self::COLUMN_comparison_value_date] = 'required|date';
                    break;
                case Segment2ConditionPropertyType::DATA_TYPE_NUMBER:
                    $rules[self::COLUMN_comparison_type] = 'required|string|in:' . implode(',', self::NUMBER_COMPARISON_TYPES);
                    $rules[self::COLUMN_comparison_value_number] = 'required|numeric';
                    break;
                case Segment2ConditionPropertyType::DATA_TYPE_BOOLEAN:
                    $rules[self::COLUMN_comparison_type] = 'required|string|in:' . implode(',', self::BOOLEAN_COMPARISON_TYPES);
                    break;
            }
        }

        return $rules;
    }

    public function fill_data($data)
    {
        /** @var Segment2ConditionPropertyType $selected_property_type */
        $selected_property_type = Segment2ConditionPropertyType::find($data[self::COLUMN_property_type_id] ?? 0);

        $this->property_type_id = $data[self::COLUMN_property_type_id] ?? 0;
        $this->property_data_type = $selected_property_type->data_type;
        $this->comparison_type = $data[self::COLUMN_comparison_type] ?? '';
        $this->comparison_value_date = $data[self::COLUMN_comparison_value_date] ?? '';
        $this->comparison_value_text = $data[self::COLUMN_comparison_value_text] ?? '';
        $this->comparison_value_number = $data[self::COLUMN_comparison_value_number] ?? 0;
    }

    public function update_condition($data)
    {
        $this->fill_data($data);
        $this->save();
    }

    public static function store_condition(Segment2Condition $condition, $data, $prefix = '')
    {
        $data = custom_validate($data, self::rules($data), $prefix);
        $self = new self();
        $self->condition_id = $condition->id;

        $self->fill_data($data);
        $self->save();
        return $self;
    }

    // region Eloquent Relationships
    public function property_type()
    {
        return $this->belongsTo(Segment2ConditionPropertyType::class, 'property_type_id');
    }

    public function condition()
    {
        return $this->belongsTo(Segment2Condition::class, 'condition_id');
    }
    // endregion
}
