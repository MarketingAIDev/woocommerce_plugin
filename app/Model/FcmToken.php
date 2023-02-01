<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FcmToken
 * @package Acelle\Model
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property integer user_id
 * @property User user
 * @property string fcm_token
 * @property boolean active
 */
class FcmToken extends Model
{
    const COLUMN_user_id = 'user_id';
    const COLUMN_fcm_token = 'fcm_token';
    const COLUMN_active = 'active';

    protected $casts = [
        self::COLUMN_active => 'boolean'
    ];

    function user()
    {
        return $this->belongsTo(User::class, self::COLUMN_user_id);
    }

    static function validateAndStore(User $user, array $input_data)
    {
        $data = custom_validate($input_data, [
            'token' => 'required|string|min:10'
        ]);
        $token = $data['token'] ?? '';
        /** @var FcmToken $model */
        $model = $user->fcm_tokens()->where(self::COLUMN_fcm_token, $token)->first();

        if($model != null)
        {
            if(!$model->active) {
                $model->active = true;
                $model->save();
            }
            return $model;
        }

        $model = new self();
        $model->fcm_token = $token;
        $model->active = true;
        $user->fcm_tokens()->save($model);
        return $model;
    }
}
