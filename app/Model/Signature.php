<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Signature
 * @package Acelle\Model
 *
 * @property string full_name
 * @property string designation
 * @property string website
 * @property string phone
 * @property string facebook
 * @property string instagram
 * @property string linkedin
 * @property string twitter
 * @property string skype
 * @property string logo_path
 *
 * Appended properties
 * @property string logo_url
 */
class Signature extends Model
{
    const COLUMN_id = "id";
    const COLUMN_uid = "uid";
    const COLUMN_customer_id = "customer_id";
    const COLUMN_content__DEPRECATED = "content";
    const COLUMN_full_name = "full_name";
    const COLUMN_designation = "designation";
    const COLUMN_website = "website";
    const COLUMN_phone = "phone";
    const COLUMN_facebook = "facebook";
    const COLUMN_instagram = "instagram";
    const COLUMN_linkedin = "linkedin";
    const COLUMN_twitter = "twitter";
    const COLUMN_skype = "skype";
    const COLUMN_youtube = "youtube";
    const COLUMN_tiktok = "tiktok";
    const COLUMN_pinterest = "pinterest";
    const COLUMN_logo_path = "logo_path";

    const PROPERTY_logo = "logo";

    protected $hidden = [
        self::COLUMN_logo_path
    ];

    protected $appends = [
        'logo_url'
    ];

    function getLogoUrlAttribute()
    {
        if ($this->logo_path) return url($this->logo_path);
        return null;
    }

    static function validateAndSave(Customer $customer, $input_data)
    {
        $data = custom_validate($input_data, [
            self::COLUMN_full_name => 'nullable|string|max:100',
            self::COLUMN_designation => 'nullable|string|max:100',
            self::COLUMN_website => 'nullable|string|max:100',
            self::COLUMN_phone => 'nullable|string|max:100',
            self::COLUMN_facebook => 'nullable|string|url|max:100',
            self::COLUMN_instagram => 'nullable|string|url|max:100',
            self::COLUMN_youtube => 'nullable|string|url|max:100',
            self::COLUMN_tiktok => 'nullable|string|url|max:100',
            self::COLUMN_pinterest => 'nullable|string|url|max:100',
            self::COLUMN_linkedin => 'nullable|string|url|max:100',
            self::COLUMN_twitter => 'nullable|string|url|max:100',
            self::COLUMN_skype => 'nullable|string|url|max:100',
            self::PROPERTY_logo => 'nullable|file|mimes:png,jpg,jpeg|max:1024',
        ], '', [
            self::COLUMN_facebook . '.url' => 'The format is invalid. The correct format is https://facebook.com/pagename',
            self::COLUMN_instagram . '.url' => 'The format is invalid. The correct format is https://instagram.com/pagename',
            self::COLUMN_youtube . '.url' => 'The format is invalid. The correct format is https://youtube.com/pagename',
            self::COLUMN_tiktok . '.url' => 'The format is invalid. The correct format is https://titkok.com/pagename',
            self::COLUMN_pinterest . '.url' => 'The format is invalid. The correct format is https://pinterest.com/pagename',  
            self::COLUMN_linkedin . '.url' => 'The format is invalid. The correct format is https://linkedin.com/pagename',
            self::COLUMN_twitter . '.url' => 'The format is invalid. The correct format is https://twitter.com/pagename',
            self::COLUMN_skype . '.url' => 'The format is invalid. The correct format is https://skype.com/pagename',
        ]);

        $model = $customer->signature;
        if (!$model) {
            $model = new self();
        }

        $file = $data[self::PROPERTY_logo] ?? null;
        if ($file) {
            $path = $file->store('signature_images', 'public');
            $model->logo_path = '/storage/' . $path;
        }

        $model->full_name = $data[self::COLUMN_full_name] ?? '';
        $model->designation = $data[self::COLUMN_designation] ?? '';
        $model->website = $data[self::COLUMN_website] ?? '';
        $model->phone = $data[self::COLUMN_phone] ?? '';
        $model->facebook = $data[self::COLUMN_facebook] ?? '';
        $model->instagram = $data[self::COLUMN_instagram] ?? '';
        $model->linkedin = $data[self::COLUMN_linkedin] ?? '';
        $model->twitter = $data[self::COLUMN_twitter] ?? '';
        $model->skype = $data[self::COLUMN_skype] ?? '';
        $model->youtube = $data[self::COLUMN_youtube] ?? '';
        $model->pinterest = $data[self::COLUMN_pinterest] ?? '';
        $model->tiktok = $data[self::COLUMN_tiktok] ?? '';

        if ($model->exists) {
            $model->save();
        } else {
            $customer->signature()->save($model);
        }

        return $model;
    }

    static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    static function boot()
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

    function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
