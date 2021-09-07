<?php

namespace Database\Factories;

use App\Models\SpotifyPlaylistTrack;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotifyPlaylistTrackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpotifyPlaylistTrack::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'spotify_playlist_id' => null,
            'track_id' => mt_rand(10000, 9999999),
            'name' => ucwords($this->faker->words(1, mt_rand(1, 3))),
            'artist' => $this->faker->name(),
            'img_url' => $this->faker->imageUrl(400, 400, 'music'),
        ];
    }
}
