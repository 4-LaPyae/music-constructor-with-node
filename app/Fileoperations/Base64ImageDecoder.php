<?php

namespace App\Fileoperations;


class Base64ImageDecoder
{
    private $base64Data;

    public function __construct($base64Data)
    {
        $this->base64Data = $base64Data;
    }

    public function decoder()
    {
        $image_parts = explode(";base64,", $this->base64Data);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        return [
            'type' => $image_type,
            'data' => $image_base64,
        ];
    }
}