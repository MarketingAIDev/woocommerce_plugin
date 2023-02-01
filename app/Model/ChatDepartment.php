<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChatDepartment
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer user_id
 * @property User user
 * @property string name
 */
class ChatDepartment extends Model
{
    protected $fillable = [
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function rules()
    {
        return [
            'name' => 'required|string|max:25'
        ];
    }

    public static function paged_search($request)
    {
        return self::where('user_id', $request->user()->id)->paginate($request->per_page ?? 20);
    }
}
