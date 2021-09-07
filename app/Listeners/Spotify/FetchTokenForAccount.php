<?php

namespace App\Listeners\Spotify;

use App\Events\UpdateUserSpotify;
use Illuminate\Contracts\Queue\ShouldQueue;

class FetchTokenForAccount
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $access_info = $event->spotify_service->getUserAccessCredentialsFromCode($event->code);
        $event->user_spotify->update($access_info);
        event(new UpdateUserSpotify($event->user_spotify, true));
    }
}
