<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @class EmailLink
 * @package Acelle\Model
 *
 * @property integer id
 * @property string uid
 * @property integer email_id
 * @property Email email
 * @property string link
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 */
class EmailLink extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'link',
    ];

    /**
     * Association with mailList through mail_list_id column.
     */
    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public function makeCopy(Email $newEmail) {
        $new = new self();
        $new->email_id = $newEmail->id;
        $new->link = $this->link;
        $new->save();
    }
}
