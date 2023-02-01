<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;

/**
 * Class GalleryImage
 * @package Acelle\Model
 *
 * @property string path
 * @property string thumbnail_path
 */
class GalleryImage extends Model
{
    const COLUMN_customer_id ='customer_id';
    const COLUMN_path ='path';
    const COLUMN_thumbnail_path ='thumbnail_path';
    const COLUMN_id ='id';

    protected $fillable = [
        'path',
    ];

    static function validateAndStore($customer, array $input_data)
    {
        custom_validate($input_data, [
            'image' => 'required|file|max:5120|mimes:png,jpg,jpeg'
        ]);

        $model = new self();
        $model->path = request('image')->store('gallery', 'public');
        $model->thumbnail_path = $model->path.'.thumb.png';

        /** @var Customer */
        $customer->gallery()->save($model);

        $public_path = public_path('storage/'.$model->path);
        $thumbnail_public_path = public_path('storage/'.$model->thumbnail_path);
        $img = Image::make($public_path)->resize(300, 185, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($thumbnail_public_path);
        return response()->json([
            'file' => $model->path,
            'thumb' => $model->thumbnail_path
        ]);
    }

    protected $appends = ['public_path', 'public_thumbnail_path'];

    public function getPublicPathAttribute(): ?string
    {
        if(empty($this->path))
            return null;
        return url('storage/' . $this->path);
    }

    public function getPublicThumbnailPathAttribute(): ?string
    {
        if(empty($this->thumbnail_path))
            return null;
        return url('storage/' . $this->thumbnail_path);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function filter($request)
    {
        $query = self::select('gallery_images.*');
        // Other filter
        if (!empty($request->customer_id)) {
            $query = $query->where('gallery_images.customer_id', '=', $request->customer_id);
        }

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
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
