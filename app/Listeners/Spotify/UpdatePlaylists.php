<?php

namespace App\Listeners\Spotify;

use App\Jobs\UpdateSpotifyPlaylistTracks;
use App\Models\SpotifyPlaylist;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePlaylists implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $current_playlist_ids = $event->user_spotify->playlists->pluck('id')->toArray();
        $playlists = $event->spotify_service->getUserPlaylists();
        $upserted_playlist_ids = [];
        foreach ($playlists->items as $playlist) {
            $playlist_image = null;
            if (! empty($playlist->images)) {
                foreach ($playlist->images as $image) {
                    $playlist_image = $image->url;
                    if ($image->width === 300) {
                        break;
                    }
                }
            }

            $playlist_data = [
                'user_spotify_id' => $event->user_spotify->id,
                'playlist_id' => $playlist->id,
                'name' => $playlist->name,
                'img_url' => $playlist_image,
                'public' => $playlist->public,
                'is_owner' => $event->user_spotify->spotify_id === $playlist->owner->id,
                'url' => isset($playlist->external_urls->spotify) ? $playlist->external_urls->spotify : null,
                'uri' => $playlist->uri,
            ];

            $spotify_playlist = SpotifyPlaylist::updateOrCreate(['playlist_id' => $playlist->id, 'user_spotify_id' => $event->user_spotify->id], $playlist_data);
            UpdateSpotifyPlaylistTracks::dispatch($spotify_playlist);
            $upserted_playlist_ids[] = $spotify_playlist->id;
        }
        $delete_ids = array_diff($current_playlist_ids, $upserted_playlist_ids);
        foreach ($delete_ids as $id) {
            $playlist = SpotifyPlaylist::find($id);
            $playlist->delete();
        }
    }
}
