<?php

use App\Http\Controllers\Web\V1\Auth\AuthController;
use App\Http\Middleware\Mobile\MobileTokenIsValid;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//======================================refactoring auth route======================================
Route::group(['prefix' => 'v1', 'middleware' => 'throttle:day'], function () {
    Route::post('/consumer/login', [AuthController::class, 'login']);
    Route::post('/consumer/register', [AuthController::class, 'register']);
});

Route::group(['prefix' => 'v1', 'middleware' => 'throttle:resend-otp-day'], function () {
    Route::post('/otp/resend', [AuthController::class, 'resendOTP']);
});

Route::group(['prefix' => 'v1'], function () {
    Route::post('/consumer/check/otp', [AuthController::class, 'checkOTP']);
    Route::post('/consumer/check/token', [AuthController::class, 'checkTokenValid']);
});

Route::group(['prefix' => 'v1', 'middleware' => MobileTokenIsValid::class], function () {
    //user information
    Route::post('/user/info', [AuthController::class, 'userInfo']);
    Route::post('/user/update/profile', [AuthController::class, 'userUpdateProfile']);
    Route::post('/user/delete', [AuthController::class, 'deleteUser']);
    Route::post('/user/logout', [AuthController::class, 'logout']);
});


//for clear rate limiter
Route::post('/rate/limiter/clear', function (Request $request) {

    RateLimiter::clear(md5('day' . $request->phone));

    return response()->json([
        'error' => false,
        'message' => 'Successfully removed from ban lists',
    ]);
});

//for clear rate limiter resend otp
Route::post('/rate/limiter/clear/resend', function (Request $request) {

    RateLimiter::clear(md5('resend-otp-day' . $request->phone));

    return response()->json([
        'error' => false,
        'message' => 'Successfully removed from ban lists.resend otp',
    ]);
});
