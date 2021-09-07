<?php

namespace App\Events;

use App\Models\UserSpotify;
use App\Services\SpotifyService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateUserSpotify
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_spotify = null;
    public $spotify_service = null;
    public $initial = false;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserSpotify $user_spotify, bool $initial = false)
    {
        $this->user_spotify = $user_spotify;
        $this->spotify_service = new SpotifyService($user_spotify);
        $this->initial = $initial;
    }
}
