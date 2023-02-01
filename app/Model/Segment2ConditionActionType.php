<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Segment2ConditionActionType
 * @package Acelle\Model
 *
 * @property integer id
 * @property string code
 * @property string name
 */
class Segment2ConditionActionType extends Model
{
    public const CODE_PLACED_ORDER = 'placed_order';
    public const CODE_ABANDONED_CART = 'abandoned_cart';
    public const CODE_SUBMITTED_REVIEW = 'submitted_review';
    public const CODE_CHATTED = 'chatted';
    public const CODE_ORDER_FULFILLED = 'order_fulfilled';
    public const CODE_ORDER_DELIVERED = 'order_delivered';
}
