<?php

namespace App\Events;

use App\Models\UserSpotify;
use App\Services\SpotifyService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpotifyFinished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user = null;
    public $user_spotify = null;
    public $spotify_service = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserSpotify $user_spotify)
    {
        $this->user_spotify = $user_spotify;
        $this->spotify_service = new SpotifyService($user_spotify);
    }

    public function broadcastAs(): string
    {
        return 'SpotifyFinished';
    }

    public function broadcastWith(): array
    {
        return [
            'success' => true,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('spotify-finished.' . $this->user_spotify->user_id);
    }
}
