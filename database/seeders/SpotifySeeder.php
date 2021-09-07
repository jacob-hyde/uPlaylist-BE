<?php

namespace Database\Seeders;

use App\Models\SpotifyPlaylist;
use App\Models\SpotifyPlaylistTrack;
use App\Models\User;
use App\Models\UserSpotify;
use Illuminate\Database\Seeder;

class SpotifySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory()->count(1000)->create();
        foreach ($users as $user) {
            $user_spotify = UserSpotify::factory()->create([
                'user_id' => $user->id,
            ]);
            for ($i = 0; $i < mt_rand(5, 30); $i++) {
                $spotify_playlist = SpotifyPlaylist::factory()->create([
                    'user_spotify' => $user_spotify->id,
                ]);
                SpotifyPlaylistTrack::factory()->count(mt_rand(10, 50))->create([
                    'spotify_playlist_id' => $spotify_playlist->id
                ]);
            }
        }
    }
}
