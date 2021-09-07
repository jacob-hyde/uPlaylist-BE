<?php

namespace App\Events;

use App\Services\SpotifyService;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpotifyAccountLinked
{
    use Dispatchable, SerializesModels;

    public $code = null;
    public $user = null;
    public $user_spotify = null;
    public $spotify_service = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $code, User $user)
    {
        $this->code = $code;
        $this->user = $user;
        if (!$user->spotify) {
            $user->spotify()->create(['user_id' => $user->id]);
            $user->load('spotify');
        }
        $this->user_spotify = $user->spotify;
        $this->spotify_service = new SpotifyService($this->user_spotify);
    }
}
