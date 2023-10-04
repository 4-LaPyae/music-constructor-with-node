<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\User;


//free auth
//for login and register
Route::group(['prefix' => 'admin'], function () {
    //login api
    Route::post('/login', [Admin\AuthController::class, 'login']);
    //register api
    Route::post('/register', [Admin\AdminController::class, 'store']);
});

//FOR USER 
//for login and register
Route::group(['prefix' => 'user'], function () {
    //login api
    Route::post('/login', [User\AuthController::class, 'login']);
    //register api
    // Route::post('/register', [User\AuthController::class, 'register']);

    Route::post('/check/otp', [User\AuthController::class, 'checkOTP']);
});
//END

Route::group(['prefix' => 'user', 'middleware' => 'throttle:resend-otp-day'], function () {
    Route::post('/resend/otp', [User\AuthController::class, 'resendOTP']);
});


// Route::group(['prefix' => 'admin', 'middleware' => ['auth:api,admin', 'throttle:api']], function () {

//     //reset password api
//     Route::post('/resetpassword/{id}', [Admin\AdminController::class, 'resetPassword']);
//     //logout api
//     Route::delete('/logout', [Admin\AuthController::class, 'logout']);
// });

Route::group(['prefix' => 'admin', 'middleware' => EnsureTokenIsValid::class], function () {

    Route::post('/resetpassword/{id}', [Admin\AdminController::class, 'resetPassword']);
    //logout api
    Route::delete('/logout', [Admin\AuthController::class, 'logout']);
});
