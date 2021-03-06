<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Curator\CuratorFeaturedPlaylistCalendarController;
use App\Http\Controllers\Api\Curator\CuratorOrderController;
use App\Http\Controllers\Api\Curator\CuratorPlaylistController;
use App\Http\Controllers\Api\Curator\UserTrackController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\HelpController;
use App\Http\Controllers\Api\SpotifyAccessController;
use App\Http\Controllers\Api\SpotifyController;
use App\Http\Controllers\Api\SpotifyPlaylistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(
    [
        'prefix' => 'v1',
        'namespace' => 'Api'
    ],
    function () {
        Route::group(['middleware' => 'auth:api'], function () {
            //----------------------------------------------------------------------------------------------
            //Auth
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
                Route::get('user', [AuthController::class, 'user']);
                Route::post('username-available', [AuthController::class, 'usernameAvailable']);
                Route::put('/', [AuthController::class, 'update']);
            });
            //----------------------------------------------------------------------------------------------
            //Spotify
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'spotify'], function () {
                Route::get('connect-url', [SpotifyAccessController::class, 'spotifyConnectUrl']);
                Route::get('refresh', [SpotifyAccessController::class, 'refreshSpotify']);
                Route::get('disconnect', [SpotifyAccessController::class, 'disconnectSpotify']);
                Route::post('/', [SpotifyAccessController::class, 'store']);
                Route::get('playlists', [SpotifyPlaylistController::class, 'index']);
                Route::put('follow/{playlist}', [SpotifyController::class, 'follow']);
            });
            //----------------------------------------------------------------------------------------------
            //Curator
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'curator'], function () {
                //----------------------------------------------------------------------------------------------
                //Playlists
                //----------------------------------------------------------------------------------------------
                Route::group(['prefix' => 'playlist'], function () {
                    Route::post('/', [CuratorPlaylistController::class, 'store']);
                    Route::get('featured-dates', [CuratorFeaturedPlaylistCalendarController::class, 'index']);
                    Route::post('promote', [CuratorFeaturedPlaylistCalendarController::class, 'store']);
                    Route::delete('{playlist}', [CuratorPlaylistController::class, 'destroy']);
                });
                //----------------------------------------------------------------------------------------------
                //Orders
                //----------------------------------------------------------------------------------------------
                Route::group(['prefix' => 'order'], function () {
                    Route::get('/', [CuratorOrderController::class, 'index']);
                    Route::put('{corder}', [CuratorOrderController::class, 'update']);
                });
            });
        });
        //----------------------------------------------------------------------------------------------
        //Auth
        //----------------------------------------------------------------------------------------------
        Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
            Route::post('register', [AuthController::class, 'register']);
            Route::post('login', [AuthController::class, 'login']);
        });

        Route::get('genre', [GenreController::class, 'index']);

        Route::get('help', [HelpController::class, 'index']);

        //----------------------------------------------------------------------------------------------
        //Curator
        //----------------------------------------------------------------------------------------------
        Route::group(['prefix' => 'curator'], function () {
            //----------------------------------------------------------------------------------------------
            //Playlists
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'playlist'], function () {
                Route::get('/', [CuratorPlaylistController::class, 'playlists']);
                Route::get('sitemap', [CuratorPlaylistController::class, 'sitemap']);
                Route::get('search', [CuratorPlaylistController::class, 'search']);
                Route::get('featured', [CuratorPlaylistController::class, 'featured']);
                Route::get('{playlist}', [CuratorPlaylistController::class, 'show']);
            });
            //----------------------------------------------------------------------------------------------
            //Order
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'order'], function () {
                Route::post('register', [CuratorOrderController::class, 'register']);
                Route::post('/', [CuratorOrderController::class, 'store']);
                Route::get('{order}', [CuratorOrderController::class, 'show']);
            });
            //----------------------------------------------------------------------------------------------
            //User Track
            //----------------------------------------------------------------------------------------------
            Route::group(['prefix' => 'user-track'], function() {
                Route::post('/', [UserTrackController::class, 'store']);
                Route::put('{user_track}', [UserTrackController::class, 'update']);
            });
        });
    }
);
