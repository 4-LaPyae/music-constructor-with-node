<?php

namespace App\Http\Controllers\Mobile\V1\Application;

use App\Http\Controllers\Controller;
use App\Models\AppSlider;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function getCountries()
    {
        $countries = Country::get(["id", "code", "country_name", "phone"]);
        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Country list',
            'data' => $countries
        ]);
    }

    public function generalVersion()
    {
        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'User Application Version',
            'data' => [
                "ios" => [
                    "minimum_version" => "1.0.0",
                    "latest_version" => "1.0.0",
                    "app_store_link" => [
                        'ios' => 'tota ios',
                    ]
                ],
                "android" => [
                    "minimum_version" => "1.0.0",
                    "latest_version" => "1.0.0",
                ],
            ]
        ]);
    }

    public function csPhone()
    {
        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'User Application CS Phone Number',
            'data' => [
                'phone' => '09750066909'
            ]
        ]);
    }

    public function serverTime()
    {
        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Tota Media Server Time',
            'data' => Carbon::now()->timezone('Asia/Yangon')->format('Y-m-d H:i:s')
        ]);
    }

    public function appBanner()
    {
        $data = [
            'https://media.wowme.tech/mobile/banner-image/banner_1.png',
            'https://media.wowme.tech/mobile/banner-image/banner_2.png',
        ];

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Wowme Banner Image',
            'data' => $data
        ]);
    }

    public function appBannerV2()
    {
        // $data = [
        //     [
        //         "image" =>  'https://media.wowme.tech/mobile/banner-image/banner_1.png',
        //         "type" => "EXTERNAL LINK",
        //         'link' => "https://www.google.com",
        //         "header" => null,
        //     ],
        //     [
        //         "image" => 'https://media.wowme.tech/mobile/banner-image/banner_2.png',
        //         "type" => "DEEP LINK",
        //         'link' => "https://www.wowme.tech/businessList/detail/M28AERL18JZ59LU",
        //         "header" => null,
        //     ],
        //     [
        //         "image" => 'https://media.wowme.tech/mobile/banner-image/banner_1.png',
        //         "type" => "IN APP WEB LINK",
        //         'link' => "https://www.wowme.tech/businessList/detail/R7W1BN396891578",
        //         "header" => "App Web Link",
        //     ],
        // ];

        $data = AppSlider::where([
            ['deleted_status', 0],
            ['suspend', 0],
        ])
            ->orderBy('id', 'desc')
            ->get(['id', 'image', 'link', 'type', 'header']);

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Wowme Banner Image',
            'data' => $data
        ]);
    }
}
