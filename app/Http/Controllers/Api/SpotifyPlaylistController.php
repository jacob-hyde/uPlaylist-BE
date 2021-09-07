<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpotifyPlaylistResource;

class SpotifyPlaylistController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->spotify) {
            return regularResponse([], false, 'User has no Spotify account', Response::HTTP_BAD_REQUEST);
        }
        $playlists = $user->spotify->playlists()
            ->with('curator_playlist')
            ->withCount('curator_playlist')
            ->withCount('tracks')
            ->where('followers', '>=', 100)
            ->where('is_owner', 1)
            ->where('public', 1)->get();

        return SpotifyPlaylistResource::collection($playlists)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
