<?php

namespace App\Listeners\Spotify;

use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAccountInformation implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $info = $event->spotify_service->getUserInfo();
        $data = [
            'spotify_id' => $info->id,
            'display_name' => $info->display_name,
            'followers' => $info->followers->total,
            'href' => $info->href,
        ];
        $event->user_spotify->update($data);
    }
}
