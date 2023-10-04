<?php


use App\Http\Controllers\Admin;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\UserEnsureTokenIsValid;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'admin', 'middleware' => EnsureTokenIsValid::class], function () {

    //logout api
    Route::delete('/logout', [Admin\AuthController::class, 'logout']);
    Route::get('search', [Admin\SongController::class, 'rawSearch']);

    Route::resource('songs', Admin\SongController::class);
    Route::get('/songdetail/{id}', [Admin\SongController::class, 'songDetailWithId']);

    Route::resource('singers', Admin\SingerController::class);
    Route::get('/singerdetail/{id}', [Admin\SingerController::class, 'singerDetailWithId']);

    Route::resource('users', Admin\UserController::class);

    Route::resource('artists', Admin\ArtistController::class);

    Route::resource('albums', Admin\AlbumController::class);

    Route::resource('producers', Admin\ProducerController::class);

    Route::post('addcontractsongs', [Admin\ProducerController::class, 'addContractSongs']);
    Route::post('updatecontractsong', [Admin\ProducerController::class, 'updateContractSongs']);
    Route::post('deletecontractsong', [Admin\ProducerController::class, 'deleteContractSong']);

    Route::resource('bands', Admin\BandController::class);
    Route::get('/share', [Admin\BandController::class, 'share']);

    Route::resource('medias', Admin\MediaController::class);
    Route::resource('distributors', Admin\DistributorController::class);
    Route::resource('recordings', Admin\RecordingController::class);

    Route::resource('groups', Admin\GroupController::class);

    Route::resource('musiclists', Admin\MusiclistController::class);
    Route::post('/deletemusiclistsong', [Admin\MusiclistController::class, 'musicListSongDelete']);
});


Route::group(['prefix' => 'user', 'middleware' => UserEnsureTokenIsValid::class], function () {

    //logout api
    Route::delete('/logout', [User\AuthController::class, 'logout']);
    Route::get('search', [Admin\SongController::class, 'rawSearch']);

    Route::resource('songs', Admin\SongController::class);
    Route::get('/songdetail/{id}', [Admin\SongController::class, 'songDetailWithId']);

    Route::resource('singers', Admin\SingerController::class);
    Route::get('/singerdetail/{id}', [Admin\SingerController::class, 'singerDetailWithId']);

    Route::resource('artists', Admin\ArtistController::class);

    Route::resource('albums', Admin\AlbumController::class);

    Route::resource('producers', Admin\ProducerController::class);

    Route::post('addcontractsongs', [Admin\ProducerController::class, 'addContractSongs']);
    Route::post('updatecontractsong', [Admin\ProducerController::class, 'updateContractSongs']);
    Route::post('deletecontractsong', [Admin\ProducerController::class, 'deleteContractSong']);

    Route::resource('bands', Admin\BandController::class);
    Route::get('/share', [Admin\BandController::class, 'share']);

    Route::resource('medias', Admin\MediaController::class);
    Route::resource('distributors', Admin\DistributorController::class);
    Route::resource('recordings', Admin\RecordingController::class);

    Route::resource('groups', Admin\GroupController::class);

    Route::resource('musiclists', Admin\MusiclistController::class);
    Route::post('/deletemusiclistsong', [Admin\MusiclistController::class, 'musicListSongDelete']);
});


// Route::get('/shared/videos/{video}', function (\Illuminate\Http\Request $request, $video) {
//     // if (!$request->hasValidSignature()) {
//     //     abort(401);
//     // }
//     return "ok nar sar";
// })->name('share-video')->middleware('signed');

// Route::get('/playground', function () {
//     $url = URL::temporarySignedRoute('share-video', now()->addSecond(30), [
//         'video' => 'qqqq'
//     ]);
//     return $url;
// });
