<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSpotifyPlaylistTracksBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $playlists;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($playlists)
    {
        $this->playlists = $playlists;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->playlists as $playlist) {
            UpdateSpotifyPlaylistTracks::dispatch($playlist);
        }
    }
}
