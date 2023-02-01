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
class Segment2ConditionLocationType extends Model
{
    const CODE_INSIDE_EU = 'inside_eu';
    const CODE_OUTSIDE_EU = 'outside_eu';
    const CODE_INSIDE_US = 'inside_eu';
    const CODE_OUTSIDE_US = 'outside_eu';

    const COUNTRY_CODES_IN_EU = [
        'AT', 'BE', 'BG' , 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU',
        'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
    ];
    const COUNTRY_CODE_US = 'US';
}
