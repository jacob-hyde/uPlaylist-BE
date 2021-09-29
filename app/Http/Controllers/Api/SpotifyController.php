<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\CuratorPlaylist;
use App\Services\SpotifyService;

class SpotifyController extends Controller
{

    public function follow($playlist)
    {
        $playlist = CuratorPlaylist::find($playlist);
        $user = auth('api')->user();
        $service = new SpotifyService($user->spotify);
        $service->followPlaylist($playlist->spotify_playlist->playlist_id);
        return regularResponse();
    }

}
