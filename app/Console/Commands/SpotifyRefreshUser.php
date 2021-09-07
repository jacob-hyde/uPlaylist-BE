<?php

namespace App\Console\Commands;

use App\Events\UpdateUserSpotify;
use App\Models\UserSpotify;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;

class SpotifyRefreshUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:refresh-user {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh user spotify information';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user_id = $this->option('user');
        if ($user_id) {
            $spotify_users = [UserSpotify::where('user_id', $user_id)->first()];
            if (empty($spotify_users)) {
                throw new Exception('User Spotify does not exist for user id: '.$user_id);
            }
            if (! $spotify_users[0]->access_token) {
                throw new Exception('Spotify personal access token does not exist for user id: '.$user_id);
            }
        } else {
            $spotify_users = UserSpotify::whereNotNull('access_token')->whereNotNull('spotify_id')->get();
        }
        foreach ($spotify_users as $user_spotify) {
            event(new UpdateUserSpotify($user_spotify));
        }
        // $user = $user_id ? User::find($user_id) : null;
        // UpdateCuratorPlaylists::dispatch($user)->onQueue('long-running-queue');
    }
}
