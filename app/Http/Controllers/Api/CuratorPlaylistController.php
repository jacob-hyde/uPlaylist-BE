<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\Curator\CuratorPlaylistResource;
use App\Models\CuratorPlaylist;
use App\Models\FeaturedPlaylistCalendar;
use App\Models\SpotifyPlaylist;

class CuratorPlaylistController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $playlist = CuratorPlaylist::where('spotify_playlist_id', $request->id)->first();
        if ($playlist) {
            $playlist->amount = convertDollarsToCents($request->price);
        } else {
            $playlist = new CuratorPlaylist();
            $playlist->curator_id = $user->curator->id;
            $playlist->spotify_playlist_id = $request->id;
            $playlist->amount = convertDollarsToCents($request->price);
        }
        $playlist->updateFromSpotify();
        $playlist->genres()->sync($request->genres);
        $playlist->save();
        return regularResponse(['message' => 'Playlist saved successfully']);
    }

    public function featured()
    {
        $featured_playlist = FeaturedPlaylistCalendar::paid()->where('date', now()->toDateString())->first();
        if (!$featured_playlist) {
            $featured_playlist = CuratorPlaylist::inRandomOrder()->first();
        }
        return (new CuratorPlaylistResource($featured_playlist->playlist))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(SpotifyPlaylist $playlist)
    {
        if ($playlist->curator_playlist) {
            $playlist->curator_playlist->delete();
        }
        return regularResponse();
    }
}
