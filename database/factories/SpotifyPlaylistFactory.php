<?php

namespace Database\Factories;

use App\Models\SpotifyPlaylist;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotifyPlaylistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpotifyPlaylist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_spotify' => null,
            'playlist_id' => mt_rand(10000, 9999999),
            'followers' => mt_rand(1, 100000),
            'img_url' => $this->faker->imageUrl(1000, 1000, 'music'),
            'public' => mt_rand(mt_rand(0, 1), 1),
            'is_owner' => mt_rand(mt_rand(0, 1), 1),
            'url' => 'https://open.spotify.com/playlist/' . mt_rand(10000, 9999999),
            'uri' => 'spotify:playlist:' . mt_rand(10000, 9999999),
        ];
    }
}
