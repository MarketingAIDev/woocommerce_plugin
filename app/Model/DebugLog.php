<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DebugLog
 * @package Acelle\Model
 *
 * @property string type
 * @property string param_1
 * @property string param_2
 * @property array data
 */
class DebugLog extends Model
{
    const COLUMN_type = "type";
    const COLUMN_param_1 = "param_1";
    const COLUMN_param_2 = "param_2";
    const COLUMN_data = "data";

    static function automation2Log(string $param_1, string $param_2, $data)
    {
        $model = new self();
        $model->type = "automation2";
        $model->param_1 = $param_1;
        $model->param_2 = $param_2;
        $model->data = $data;
        $model->save();
    }

    protected $casts = [
        self::COLUMN_data => 'array'
    ];
}
