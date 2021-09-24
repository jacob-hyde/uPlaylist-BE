<?php

namespace App\Listeners\Spotify;

use App\Events\SpotifyFinished;
use Illuminate\Contracts\Queue\ShouldQueue;

class FinishedSpotifyConnect implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->initial) {
            // event(new SpotifyFinished($event->user_spotify));
        }
    }
}
