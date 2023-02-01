<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChatCannedMessage
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer user_id
 * @property User user
 * @property string name
 * @property string message
 */
class ChatCannedMessage extends Model
{
    protected $fillable = [
        'name',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function rules()
    {
        return [
            'name' => 'required|string|max:25',
            'message' => 'required|string'
        ];
    }

    public static function paged_search($request)
    {
        return self::where('user_id', $request->user()->id)->paginate($request->per_page ?? 20);
    }
}
