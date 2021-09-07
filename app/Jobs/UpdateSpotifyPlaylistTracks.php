<?php

namespace App\Jobs;

use App\Models\SpotifyPlaylist;
use App\Models\SpotifyPlaylistTrack;
use App\Services\SpotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSpotifyPlaylistTracks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $_playlist = null;
    private $_user_spotify = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SpotifyPlaylist $playlist)
    {
        $this->_playlist = $playlist;
        $this->_user_spotify = $playlist->user_spotify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $current_track_ids = $this->_playlist->tracks->pluck('id')->toArray();
        $spotify_service = new SpotifyService($this->_user_spotify);
        $playlist_details = $spotify_service->getUserPlaylistDetails($this->_playlist->playlist_id);
        if (! $playlist_details) {
            return;
        }
        $this->_playlist->update(['followers' => $playlist_details->followers->total]);
        $upserted_track_ids = [];
        $track_artist = '';
        foreach ($playlist_details->tracks->items as $item) {
            if (! $item->track || ! $item->track->id) {
                continue;
            }
            if (! isset($item->track->artists)) {
                continue;
            }
            $track_artist = $item->track->artists[0]->name;
            foreach ($item->track->artists as $artist) {
                if ($artist->type === 'artist') {
                    $track_artist = $artist->name;
                    break;
                }
            }
            $track_image = null;
            if (! empty($item->track->album->images)) {
                foreach ($item->track->album->images as $image) {
                    $track_image = $image->url;
                    if ($image->width === 300) {
                        break;
                    }
                }
            }
            $track_data = [
                'spotify_playlist_id' => $this->_playlist->id,
                'track_id' => $item->track->id,
                'name' => $item->track->name,
                'artist' => $track_artist,
                'img_url' => $track_image,
            ];
            $playlist_track = SpotifyPlaylistTrack::updateOrCreate(['spotify_playlist_id' => $this->_playlist->id,
                                                                    'track_id' => $item->track->id, ], $track_data);
            $upserted_track_ids[] = $playlist_track->id;
        }
        $delete_ids = array_diff($current_track_ids, $upserted_track_ids);
        SpotifyPlaylistTrack::whereIn('id', $delete_ids)->delete();
    }
}
