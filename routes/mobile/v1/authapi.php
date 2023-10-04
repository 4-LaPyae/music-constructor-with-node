<?php

use App\Http\Controllers\Mobile\V1\Application;
use App\Http\Controllers\Mobile\V1\Auth\AuthController;
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
    Route::post('/consumer/check/device', [AuthController::class, 'checkDevice']);
});

Route::group(['prefix' => 'v1', 'middleware' => MobileTokenIsValid::class], function () {
    //user information
    Route::post('/user/info', [AuthController::class, 'userInfo']);
    Route::post('/user/update/profile', [AuthController::class, 'userUpdateProfile']);
    Route::post('/user/delete/profile', [AuthController::class, 'userDeleteProfile']);
    Route::post('/user/delete', [AuthController::class, 'deleteUser']);
    Route::post('/user/logout', [AuthController::class, 'logout']);

    Route::get('search', [Application\SongController::class, 'rawSearch']);
    Route::resource('groups', Application\GroupController::class);
    Route::resource('songs', Application\SongController::class);
    Route::resource('albums', Application\AlbumController::class);
    Route::resource('singers', Application\SingerController::class);
    Route::resource('musiclists', Application\MusiclistController::class);

    Route::resource('playlists', Application\PlaylistController::class);
    Route::post('/addsongstoplaylist', [Application\PlaylistController::class, 'addSongsPlaylist']);
    Route::post('/deletesongstoplaylist', [Application\PlaylistController::class, 'deleteSongsPlaylist']);

    Route::get('nextsong', [Application\SongController::class, 'nextSong']);

    //Route::get('/user/play', [SongController::class, 'play']);
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
