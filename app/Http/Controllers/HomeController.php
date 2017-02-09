<?php

namespace App\Http\Controllers;

use App\Image;
use File;
use Response;
use \Imagine\Image\Box;

class HomeController extends Controller
{
    public function imgShow(string $any = '')
    {
        list($id, $file_name)       = explode('-', $any);
        list($size_url, $type_name) = explode('.', $file_name);
        $size                       = sscanf($size_url, 'w%dh%d');
        $flag                       = [];

        if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            // $type_name = 'webp';
        }

        $image_db = Image::findOrFail($id);
        if (!$size[0]) {
            $size[0] = $image_db->width;
        }
        for ($i = 0; $i < 10; $i++) {
            $max = $image_db->width * pow(2, (5 - $i));
            if ($size[0] > $max) {
                $size[0] = $image_db->width * pow(2, (5 - $i + 1));
                break;
            }
        }
        //dd($size, $i);

        if (!$size[1]) {
            $size[1]            = $size[0] / $image_db->width * $image_db->height;
            $flag['autoheight'] = true;
        }

        $size_public = 'w' . $size[0];
        $size_public .= isset($flag['autoheight']) ? '' : 'h' . $size[1];
        $path = public_path('img/' . $id . '-' . $size_public . '.' . $type_name);
        if (File::exists($path)) {
            $fileType = File::type($path);
            $response = Response::make(File::get($path), 200);
            $response->header("Content-Type", $fileType);
            return $response;
        }
        //dd(File::exists($path), $path);

        $imagine = new \Imagine\Gd\Imagine();
        $image   = $imagine->open(storage_path("images/product-224.jpg"));

        $image->resize(new Box($size[0], $size[1]))
            ->save($path);

        dd($id, $file_name, $any, $size);
        return '';
    }
}
