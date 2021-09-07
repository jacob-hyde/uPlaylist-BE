<?php

namespace Database\Factories;

use App\Models\UserSpotify;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSpotifyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSpotify::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => null,
            'spotify_id' => mt_rand(1000, 999999),
            'spotify_artist_id' => mt_rand(1000, 999999),
            'display_name' => $this->faker->userName(),
            'followers' => mt_rand(100, 999999),
            'href' => 'https://api.spotify.com/v1/users/' . mt_rand(1000, 999999),
            'access_token' => 'token',
            'refresh_token' => 'refresh',
            'token_expires' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }
}
