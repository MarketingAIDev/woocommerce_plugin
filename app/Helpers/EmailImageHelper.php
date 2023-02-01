<?php

namespace Acelle\Helpers;

use finfo;
use Illuminate\Support\Facades\URL;

class EmailImageHelper
{
    static function saveImage($returnPrefix, $imageSaveLocation, $image)
    {
        if (substr($image, 0, 4) == 'http') return join_paths($returnPrefix, self::saveImageFromUrl($imageSaveLocation, $image));
        if (strpos($image, ';base64,') !== FALSE) return join_paths($returnPrefix, self::saveBase64Image($imageSaveLocation, $image));
        if (substr($image, 0, strlen($returnPrefix)) != $returnPrefix) return join_paths($returnPrefix, self::saveImagesFromPublic($imageSaveLocation, $image));
        return $image;
    }

    static function saveBase64Image($imageSaveLocation, $imageData): string
    {
        $image_parts = explode(";base64,", $imageData);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image = base64_decode($image_parts[1]);

        $image_name = uniqid('img.') . '.' . $image_type;
        $image_location = join_paths($imageSaveLocation, $image_name);
        if(!file_exists($imageSaveLocation))
            mkdir($imageSaveLocation, 0777, true);
        file_put_contents($image_location, $image);

        return $image_name;
    }

    static function saveImageFromUrl($imageSaveLocation, $imageURL): string
    {
        $image = file_get_contents($imageURL);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_string = $finfo->buffer($image);
        $mime = explode(';', $mime_string)[0];
        $mime_split = explode('/', $mime);
        $image_type = count($mime_split) > 1 ? $mime_split[1] : 'png';

        $image_name = uniqid('img.') . '.' . $image_type;
        $image_location = join_paths($imageSaveLocation, $image_name);

        if(!file_exists($imageSaveLocation))
            mkdir($imageSaveLocation, 0777, true);
        file_put_contents($image_location, file_get_contents($imageURL));

        return $image_name;
    }


    static function saveImagesFromPublic($imageSaveLocation, $imagePath): string
    {
        $image_full_path = URL::asset($imagePath);

        return self::saveImageFromUrl($imageSaveLocation, $image_full_path);
    }
}