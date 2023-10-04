<?php

// use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

function store_img($img)
{

    [2 => $controller] = explode('/', Request()->path());

    $file_name = Str::uuid() . '_' . $controller . '.' . $img->extension();
    $path = $img->storeAs('images/' . $controller, $file_name, 'public');

    return 'storage/' . $path;;
}


function store_img64($img)
{

    [2 => $controller] = explode('/', Request()->path());
    $image_parts = explode(";base64,", $img);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    $folderPath = 'public/images/' . $controller . '/';
    $file_name = $folderPath .  Str::uuid() . '.' . $image_type;
    Storage::put($file_name, $image_base64);
    $image = Storage::url($file_name);
    return $image;
}

function store_businessGalleries64($img)
{

    [2 => $controller] = explode('/', Request()->path());
    $image_parts = explode(";base64,", $img);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    $folderPath = 'public/images/' . $controller . '/galleries/';
    $file_name = $folderPath .  Str::uuid() . '.' . $image_type;
    Storage::put($file_name, $image_base64);
    $image = Storage::url($file_name);
    return $image;
}

// function store_companyGalleries64($img)
// {

//     [2 => $controller] = explode('/', Request()->path());
//     $image_parts = explode(";base64,", $img);
//     $image_type_aux = explode("image/", $image_parts[0]);
//     $image_type = $image_type_aux[1];
//     $image_base64 = base64_decode($image_parts[1]);
//     $folderPath = 'public/images/' . $controller . '/galleries/';
//     $file_name = $folderPath .  Str::uuid() . '.' . $image_type;
//     Storage::put($file_name, $image_base64);
//     $image = Storage::url($file_name);
//     return $image;
// }

function rand_str($length = 7, $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ1234567890')
{
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars[rand(0, $chars_length)];

    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string)) {
        // Grab a random character from our list
        $r = $chars[rand(0, $chars_length)];

        // Make sure the same two characters don't appear next to each other
        if ($r != $string[$i - 1]) $string .=  $r;
    }

    // Return the string
    return $string;
}

function generateUUID($length)
{
    $random = '';
    for ($i = 0; $i < $length; $i++) {
        $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('A'), ord('Z')));
    }
    return $random;
}

function randomStr($length = 7, $chars = '123456789abcdefghijklmnopqrstuvwxyz')
{
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars[rand(1, $chars_length)];

    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string)) {
        // Grab a random character from our list
        $r = $chars[rand(1, $chars_length)];

        // Make sure the same two characters don't appear next to each other
        if ($r != $string[$i - 1]) $string .=  $r;
    }

    // Return the string
    return $string;
}
