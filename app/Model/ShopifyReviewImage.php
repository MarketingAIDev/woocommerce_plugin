<?php

namespace Acelle\Model;

use finfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

/**
 * @property integer id
 * @property integer review_id
 * @property ShopifyReview review
 * @property string image_path
 */
class ShopifyReviewImage extends Model
{
    public const COLUMN_id = 'id';
    public const COLUMN_review_id = 'review_id';
    public const COLUMN_image_path = 'image_path';

    protected $appends = ['full_path'];
    protected $hidden = ['image_path'];

    public function getFullPathAttribute()
    {
        return URL::asset($this->image_path);
    }

    public static function store_image(ShopifyReview $review, $image)
    {
        $path = $image->store('review_images', 'public');

        $model = new self();
        $model->review_id = $review->id;
        $model->image_path = '/storage/' . $path;
        $review->images()->save($model);
    }

    public static function store_image_from_url(ShopifyReview $review, $image_url)
    {
        $mime_provider = MimeTypes::getDefault();
        $content = file_get_contents($image_url, false);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        $storage_path = 'review_images/' . Str::random(40) . '.' . $mime_provider->getExtensions($mime)[0] ?? 'jpeg';
        Storage::put('public/'.$storage_path, $content);

        $model = new self();
        $model->review_id = $review->id;
        $model->image_path = '/storage/' . $storage_path;
        $review->images()->save($model);
    }

    public function review()
    {
        return $this->belongsTo(ShopifyReview::class, self::COLUMN_review_id);
    }
}
