<?php

use App\Http\Controllers\Mobile\V1\Application;
use App\Http\Middleware\Mobile\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;



// Route::group(['prefix' => 'v1', 'middleware' => EnsureTokenIsValid::class], function () {
Route::group(['prefix' => 'v1'], function () {
    Route::get('application/getcountries', [Application\ApplicationController::class, 'getCountries']);
    Route::get('application/version/general', [Application\ApplicationController::class, 'generalVersion']);
    Route::get('application/cs/phone', [Application\ApplicationController::class, 'csPhone']);
    Route::get('application/server/time', [Application\ApplicationController::class, 'serverTime']);
   // Route::get('application/payment/type', [Application\ApplicationController::class, 'paymentCardType']);
    Route::get('application/banner', [Application\ApplicationController::class, 'appBanner']);
      
});


Route::group(['prefix' => 'v2'], function () {
    Route::get('user/application/banner', [ApplicationController::class, 'appBannerV2']);
});
