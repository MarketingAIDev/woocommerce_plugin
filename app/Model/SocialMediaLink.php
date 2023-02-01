<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class SocialMediaLink extends Model
{
    protected $fillable = [
        'type',
        'full_link',
    ];

    public static function createRules()
    {
        return [
            'type' => 'required|in:facebook,instagram,twitter',
            'full_link' => 'required|url',
        ];
    }

    public static function updateRules()
    {
        return [
            'type' => 'required|in:facebook,instagram,twitter',
            'full_link' => 'required|url',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }
}
