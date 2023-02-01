<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionAction
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer condition_id
 * @property Segment2Condition condition
 *
 * @property integer action_type_id
 * @property Segment2ConditionActionType action_type
 * @property string count_type
 * @property integer count_value
 * @property string date_type
 * @property string date_value_date_one
 * @property string date_value_date_two
 * @property string date_value_int_one
 * @property string date_value_int_two
 * @property string date_period
 */
class Segment2ConditionAction extends Model
{
    public const COLUMN_id = 'id';
    public const COLUMN_condition_id = 'condition_id';
    public const COLUMN_action_type_id = 'action_type_id';
    public const COLUMN_count_type = 'count_type';
    public const COLUMN_count_value = 'count_value';
    public const COLUMN_date_type = 'date_type';
    public const COLUMN_date_value_one___DEPRECATED = 'date_value_one';
    public const COLUMN_date_value_two___DEPRECATED = 'date_value_two';
    public const COLUMN_date_value_date_one = 'date_value_date_one';
    public const COLUMN_date_value_date_two = 'date_value_date_two';
    public const COLUMN_date_value_int_one = 'date_value_int_one';
    public const COLUMN_date_value_int_two = 'date_value_int_two';
    public const COLUMN_date_period = 'date_period';

    // region Constants
    public const COUNT_TYPE_ZERO_TIMES = 'zero_times';
    public const COUNT_TYPE_AT_LEAST_ONCE = 'at_least_once';
    public const COUNT_TYPE_AT_MOST_ONCE = 'at_most_once';
    public const COUNT_TYPE_EQUAL_TO = 'equal_to';
    public const COUNT_TYPE_NOT_EQUAL_TO = 'not_equal_to';
    public const COUNT_TYPE_AT_LEAST = 'at_least';
    public const COUNT_TYPE_AT_MOST = 'at_most';
    public const COUNT_TYPE_LESS_THAN = 'less_than';
    public const COUNT_TYPE_GREATER_THAN = 'greater_than';
    public const COUNT_TYPES = [
        self::COUNT_TYPE_ZERO_TIMES,
        self::COUNT_TYPE_AT_LEAST_ONCE,
        self::COUNT_TYPE_AT_MOST_ONCE,
        self::COUNT_TYPE_EQUAL_TO,
        self::COUNT_TYPE_NOT_EQUAL_TO,
        self::COUNT_TYPE_AT_LEAST,
        self::COUNT_TYPE_AT_MOST,
        self::COUNT_TYPE_LESS_THAN,
        self::COUNT_TYPE_GREATER_THAN,
    ];
    public const COUNT_TYPES_VALUE_REQUIRED = [
        self::COUNT_TYPE_EQUAL_TO,
        self::COUNT_TYPE_NOT_EQUAL_TO,
        self::COUNT_TYPE_AT_LEAST,
        self::COUNT_TYPE_AT_MOST,
        self::COUNT_TYPE_LESS_THAN,
        self::COUNT_TYPE_GREATER_THAN,
    ];

    public const DATE_TYPE_OVER_ALL_TIME = 'over_all_time';
    public const DATE_TYPE_IN_THE_LAST = 'in_the_last';
    public const DATE_TYPE_BETWEEN = 'between';
    public const DATE_TYPE_BEFORE_DATE = 'before_date';
    public const DATE_TYPE_AFTER_DATE = 'after_date';
    public const DATE_TYPE_BETWEEN_DATES = 'between_dates';
    public const DATE_TYPES = [
        self::DATE_TYPE_OVER_ALL_TIME,
        self::DATE_TYPE_IN_THE_LAST,
        self::DATE_TYPE_BETWEEN,
        self::DATE_TYPE_BEFORE_DATE,
        self::DATE_TYPE_AFTER_DATE,
        self::DATE_TYPE_BETWEEN_DATES,
    ];

    public const DATE_TYPES_PERIOD_REQUIRED = [
        self::DATE_TYPE_IN_THE_LAST,
        self::DATE_TYPE_BETWEEN,
    ];

    public const DATE_TYPES_VALUE_1_INTEGER_REQUIRED = [
        self::DATE_TYPE_IN_THE_LAST,
        self::DATE_TYPE_BETWEEN,
    ];

    public const DATE_TYPES_VALUE_1_DATE_REQUIRED = [
        self::DATE_TYPE_BEFORE_DATE,
        self::DATE_TYPE_AFTER_DATE,
        self::DATE_TYPE_BETWEEN_DATES,
    ];

    public const DATE_TYPES_VALUE_2_INTEGER_REQUIRED = [
        self::DATE_TYPE_BETWEEN,
    ];

    public const DATE_TYPES_VALUE_2_DATE_REQUIRED = [
        self::DATE_TYPE_BETWEEN_DATES,
    ];

    public const DATE_PERIOD_DAYS = 'days';
    public const DATE_PERIOD_WEEKS = 'weeks';
    public const DATE_PERIOD_MONTHS = 'months';
    public const DATE_PERIODS = [
        self::DATE_PERIOD_DAYS,
        self::DATE_PERIOD_WEEKS,
        self::DATE_PERIOD_MONTHS,
    ];
    // endregion

    protected $appends = [
        'condition_type'
    ];

    protected $casts = [
        self::COLUMN_date_value_date_one => 'date:Y-m-d',
        self::COLUMN_date_value_date_two => 'date:Y-m-d',
    ];

    public function getConditionTypeAttribute()
    {
        return 'action';
    }

    /**
     * @param Builder $original_query
     */
    public function applyFilter($original_query)
    {
        switch ($this->action_type->code) {
            case Segment2ConditionActionType::CODE_PLACED_ORDER:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->whereHas('shopify_orders', function (Builder $order_query) {
                        $this->applyDateFilter($order_query, 'shopify_created_at');
                    }, $this->getCountFilterOperator(), $this->getCountFilterValue());
                });
                break;
            case Segment2ConditionActionType::CODE_ABANDONED_CART:
                $original_query->whereHas('shopify_customer', function (Builder $customer_query) {
                    $customer_query->whereHas('shopify_checkouts', function (Builder $order_query) {
                        $order_query->whereNull('shopify_completed_at');
                        $this->applyDateFilter($order_query, 'shopify_created_at');
                    }, $this->getCountFilterOperator(), $this->getCountFilterValue());
                });
                break;
            case Segment2ConditionActionType::CODE_ORDER_FULFILLED:
            case Segment2ConditionActionType::CODE_ORDER_DELIVERED:
                break;
            case Segment2ConditionActionType::CODE_SUBMITTED_REVIEW:
                $original_query->whereHas('shopify_reviews', function (Builder $review_query) {
                    $this->applyDateFilter($review_query);
                }, $this->getCountFilterOperator(), $this->getCountFilterValue());
                break;
            case Segment2ConditionActionType::CODE_CHATTED:
                $original_query->whereHas('chat_sessions', function (Builder $chat_query) {
                    $this->applyDateFilter($chat_query);
                }, $this->getCountFilterOperator(), $this->getCountFilterValue());
                break;
        }
    }

    /**
     * @param Builder $query
     * @param string $date_column
     */
    function applyDateFilter($query, $date_column = 'created_at')
    {
        switch ($this->date_type) {
            case self::DATE_TYPE_OVER_ALL_TIME:
                // no need to anything
                break;
            case self::DATE_TYPE_IN_THE_LAST:
                $period_start = Carbon::now();
                switch ($this->date_period) {
                    case self::DATE_PERIOD_DAYS:
                        $period_start->subDays($this->date_value_int_one);
                        break;
                    case self::DATE_PERIOD_WEEKS:
                        $period_start->subWeeks($this->date_value_int_one);
                        break;
                    case self::DATE_PERIOD_MONTHS:
                        $period_start->subMonths($this->date_value_int_one);
                        break;
                }
                $query->where($date_column, '>=', $period_start);
                break;
            case self::DATE_TYPE_BETWEEN:
                $period_start = Carbon::now();
                $period_end = Carbon::now();
                switch ($this->date_period) {
                    case self::DATE_PERIOD_DAYS:
                        $period_start->subDays($this->date_value_int_one);
                        $period_end->subDays($this->date_value_int_two);
                        break;
                    case self::DATE_PERIOD_WEEKS:
                        $period_start->subWeeks($this->date_value_int_one);
                        $period_end->subWeeks($this->date_value_int_two);
                        break;
                    case self::DATE_PERIOD_MONTHS:
                        $period_start->subMonths($this->date_value_int_one);
                        $period_end->subMonths($this->date_value_int_two);
                        break;
                }
                $query->where($date_column, '>=', $period_start)
                    ->where($date_column, '<=', $period_end);
                break;
            case self::DATE_TYPE_BEFORE_DATE:
                $period_end = Carbon::parse($this->date_value_date_one);
                $query->where($date_column, '<=', $period_end);
                break;
            case self::DATE_TYPE_AFTER_DATE:
                $period_start = Carbon::parse($this->date_value_date_one);
                $query->where($date_column, '>=', $period_start);
                break;
            case self::DATE_TYPE_BETWEEN_DATES:
                $period_start = Carbon::parse($this->date_value_date_one);
                $period_end = Carbon::parse($this->date_value_date_two);
                $query->where($date_column, '>=', $period_start)
                    ->where($date_column, '<=', $period_end);
                break;
        }
    }

    function getCountFilterOperator(): string
    {
        switch ($this->count_type) {
            case self::COUNT_TYPE_AT_LEAST:
            case self::COUNT_TYPE_AT_LEAST_ONCE:
                return ">=";
            case self::COUNT_TYPE_AT_MOST:
            case self::COUNT_TYPE_AT_MOST_ONCE:
                return "<=";
            case self::COUNT_TYPE_NOT_EQUAL_TO:
                return "!=";
            case self::COUNT_TYPE_LESS_THAN:
                return "<";
            case self::COUNT_TYPE_GREATER_THAN:
                return ">";
            case self::COUNT_TYPE_ZERO_TIMES:
            case self::COUNT_TYPE_EQUAL_TO:
            default:
                return "=";
        }
    }

    function getCountFilterValue()
    {
        switch ($this->count_type) {
            case self::COUNT_TYPE_EQUAL_TO:
            case self::COUNT_TYPE_NOT_EQUAL_TO:
            case self::COUNT_TYPE_AT_LEAST:
            case self::COUNT_TYPE_AT_MOST:
            case self::COUNT_TYPE_LESS_THAN:
            case self::COUNT_TYPE_GREATER_THAN:
                return $this->count_value;
            case self::COUNT_TYPE_AT_LEAST_ONCE:
            case self::COUNT_TYPE_AT_MOST_ONCE:
                return 1;
            case self::COUNT_TYPE_ZERO_TIMES:
            default:
                return 0;
        }
    }

    public static function rules($data)
    {
        $rules = [
            self::COLUMN_action_type_id => 'required|integer|exists:segment2_condition_action_types,id',
            self::COLUMN_count_type => 'required|string|in:' . implode(',', self::COUNT_TYPES),
            self::COLUMN_date_type => 'required|string|in:' . implode(',', self::DATE_TYPES),
        ];

        $selected_count_type = $data[self::COLUMN_count_type] ?? '';
        if (in_array($selected_count_type, self::COUNT_TYPES_VALUE_REQUIRED)) {
            $rules[self::COLUMN_count_value] = 'required|integer|min:0';
        }

        $selected_date_type = $data[self::COLUMN_date_type] ?? '';
        if (in_array($selected_date_type, self::DATE_TYPES_VALUE_1_INTEGER_REQUIRED)) {
            $rules[self::COLUMN_date_value_int_one] = 'required|integer|min:0';
        }
        if (in_array($selected_date_type, self::DATE_TYPES_VALUE_2_INTEGER_REQUIRED)) {
            $rules[self::COLUMN_date_value_int_two] = 'required|integer|min:0';
        }
        if (in_array($selected_date_type, self::DATE_TYPES_VALUE_1_DATE_REQUIRED)) {
            $rules[self::COLUMN_date_value_date_one] = 'required|date';
        }
        if (in_array($selected_date_type, self::DATE_TYPES_VALUE_2_DATE_REQUIRED)) {
            $rules[self::COLUMN_date_value_date_two] = 'required|date';
        }
        if (in_array($selected_date_type, self::DATE_TYPES_PERIOD_REQUIRED)) {
            $rules[self::COLUMN_date_period] = 'required|string|in:' . implode(',', self::DATE_PERIODS);
        }

        return $rules;
    }

    public static function store_condition(Segment2Condition $condition, $data, $prefix = '')
    {
        $data = custom_validate($data, self::rules($data), $prefix);
        $model = new self();
        $model->condition_id = $condition->id;
        $model->fill_data($data);
        $model->save();
        return $model;
    }

    public function update_condition($data)
    {
        $this->fill_data($data);
        $this->save();
        return $this;
    }

    public function fill_data($data)
    {
        $this->action_type_id = $data[self::COLUMN_action_type_id] ?? 0;
        $this->count_type = $data[self::COLUMN_count_type] ?? '';
        $this->count_value = $data[self::COLUMN_count_value] ?? 0;
        $this->date_type = $data[self::COLUMN_date_type] ?? '';
        $this->date_value_date_one = $data[self::COLUMN_date_value_date_one] ?? '';
        $this->date_value_date_two = $data[self::COLUMN_date_value_date_two] ?? '';
        $this->date_value_int_one = $data[self::COLUMN_date_value_int_one] ?? 0;
        $this->date_value_int_two = $data[self::COLUMN_date_value_int_two] ?? 0;
        $this->date_period = $data[self::COLUMN_date_period] ?? '';
    }

    // region Eloquent Relationships
    public function condition()
    {
        return $this->belongsTo(Segment2Condition::class, 'condition_id');
    }

    public function action_type()
    {
        return $this->belongsTo(Segment2ConditionActionType::class, 'action_type_id');
    }
    // endregion
}
