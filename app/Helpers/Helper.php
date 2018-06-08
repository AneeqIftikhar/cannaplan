<?php
namespace CannaPlan\Helpers;


class Helper
{
    public static function createImageUniqueName($extension)
    {
        $unique_id = time() . uniqid(rand());
        $image_name = $unique_id . '.' . $extension;

        return $image_name;
    }
    public static function uploadImage($file)
    {
        $image_name=Helper::createImageUniqueName($file->getClientOriginalExtension());
        $file->move(public_path('images'),$image_name);
        return 'public/images/'.$image_name;
    }
}