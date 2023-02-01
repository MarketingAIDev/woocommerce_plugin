<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @class Attachment
 * @package Acelle\Model
 *
 * @property integer id
 * @property string uid
 * @property integer email_id
 * @property Email email
 * @property string name
 * @property string file
 * @property string size
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 */
class Attachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'size',    ];

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

    public function remove()
    {
        unlink($this->file);
        $this->delete();
    }

    public function makeCopy(Email $newEmail) {
        $previous_folder = $this->email->getStoragePath();
        $new_folder = $newEmail->getStoragePath();

        $new_file = str_replace($previous_folder, $new_folder, $this->file);
        Storage::copy($this->file, $new_file);
        $new = new self();
        $new->email_id = $newEmail->id;
        $new->name = $this->name;
        $new->file = $new_file;
        $new->size = $this->size;
        $new->save();
    }
}
