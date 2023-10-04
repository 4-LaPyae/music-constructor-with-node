<?php

namespace App\Fileoperations;

use MongoDB\BSON\ObjectId;


class MediaOperation
{
    public static function produceMedia($operation)
    {
        return $operation->media =  (object)[
            'id' => $operation->media['id']->__toString(),
            'media_link' => $operation->media['media_link']
        ];
    }

    // public static function producePhoto($operation)
    // {
    //     return $operation->photo =  (object)[
    //         'id' => $operation->photo['id']->__toString(),
    //         'media_link' => $operation->photo['media_link']
    //     ];
    // }

    public static function produceGroupMedia($operation)
    {
        return $operation->group_image =  (object)[
            'id' => $operation->group_image['id']->__toString(),
            'media_link' => $operation->group_image['media_link']
        ];
    }

    public static function produceFront($operation)
    {
        return $operation->front_cover =  (object)[
            'id' => $operation->front_cover['id']->__toString(),
            'media_link' => $operation->front_cover['media_link']
        ];
    }
    public static function produceBack($operation)
    {
        return $operation->back_cover =  (object)[
            'id' => $operation->back_cover['id']->__toString(),
            'media_link' => $operation->back_cover['media_link']
        ];
    }

    public static function producePoster($operation)
    {
        return $operation->poster =  (object)[
            'id' => $operation->poster['id']->__toString(),
            'media_link' => $operation->poster['media_link']
        ];
    }


    public static function produceContract($contractArr)
    {

        $contracts =  array_map(function ($element) {
            $element = [
                "id" => $element['id']->__toString(),
                "media_link" => $element['media_link'],
            ];
            return $element;
        }, $contractArr);

        return $contracts;
    }
}
