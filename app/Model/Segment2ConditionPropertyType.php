<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionPropertyType
 * @package Acelle\Model
 *
 * @property integer id
 * @property string code
 * @property string name
 * @property string data_type
 */
class Segment2ConditionPropertyType extends Model
{
    protected $appends = ['comparison_operators'];
    public const DATA_TYPE_TEXT = 'text';
    public const DATA_TYPE_DATE = 'date';
    public const DATA_TYPE_NUMBER = 'number';
    public const DATA_TYPE_BOOLEAN = 'boolean';
    public const DATA_TYPES = [
        self::DATA_TYPE_TEXT,
        self::DATA_TYPE_DATE,
        self::DATA_TYPE_NUMBER,
        self::DATA_TYPE_BOOLEAN,
    ];

    public const CODE_ACCEPTS_MARKETING = 'accepts_marketing';
    public const CODE_ACCEPTS_MARKETING_UPDATE_DATE = 'accepts_marketing_update_date';
    public const CODE_CURRENCY = 'currency';
    public const CODE_JOINING_DATE = 'joining_date';
    public const CODE_EMAIL_ADDRESS = 'email_address';
    public const CODE_FIRST_NAME = 'first_name';
    public const CODE_LAST_NAME = 'last_name';
    public const CODE_NOTE = 'note';
    public const CODE_NUMBER_OF_ORDERS = 'number_of_orders';
    public const CODE_PHONE = 'phone';
    public const CODE_ACCOUNT_STATE = 'account_state';
    public const CODE_TOTAL_SPENT = 'total_spent';
    public const CODE_VERIFIED_EMAIL = 'verified_email';


    public function getComparisonOperatorsAttribute(){
        switch ($this->data_type){
            case self::DATA_TYPE_TEXT:
                return Segment2ConditionProperty::TEXT_COMPARISON_TYPES;
            case self::DATA_TYPE_DATE:
                return Segment2ConditionProperty::DATE_COMPARISON_TYPES;
            case self::DATA_TYPE_BOOLEAN:
                return Segment2ConditionProperty::BOOLEAN_COMPARISON_TYPES;
            case self::DATA_TYPE_NUMBER:
                return Segment2ConditionProperty::NUMBER_COMPARISON_TYPES;
        }
        return [];
    }
}
