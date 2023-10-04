<?php

namespace App\Http\Controllers\Web\V1\Application;

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
                    "minimum_version" => "1.0.4",
                    "latest_version" => "1.0.4",
                    "app_store_link" => [
                        'ios' => 'tota ios',
                    ]
                ],
                "android" => [
                    "minimum_version" => "1.0.4",
                    "latest_version" => "1.0.4",
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
            'message' => 'Wowme Server Time',
            'data' => Carbon::now()->timezone('Asia/Yangon')->format('Y-m-d H:i:s')
        ]);
    }

    public function appBanner()
    {
        $data = [
            "https://core.totamedia.com/images/banner-images/banner-1.png",
            "https://core.totamedia.com/images/banner-images/banner-2.png",
            "https://core.totamedia.com/images/banner-images/banner-3.png",
        ];

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Banner Image',
            'data' => $data
        ]);
    }
}
