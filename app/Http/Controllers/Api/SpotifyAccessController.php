<?php

namespace App\Http\Controllers\Api;

use App\Events\SpotifyAccountLinked;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SpotifyAccessController extends Controller
{
    public function spotifyConnectUrl(Request $request)
    {
        $user = auth()->user();
        $verifier_bytes = random_bytes(64);
        $code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");
        $challenge_bytes = hash("sha256", $code_verifier, true);
        $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");
        $data = [
            'redirect_url' => config('services.spotify.redirect'),
            'code_verifier' => $code_verifier,
            'code_challenge' => $code_challenge,
        ];
        Cache::put($user->id . 'spotify_connect', $data);
        $url = 'https://accounts.spotify.com/authorize?client_id=' .
            config('services.spotify.client_id') .
            '&response_type=code&code_challenge_method=S256&code_challenge=' .
            $data['code_challenge'] .
            '&scope=user-read-email%20playlist-read-collaborative%20playlist-modify-private%20playlist-modify-public%20user-follow-read%20user-follow-modify' .
            '&redirect_uri=' . config('services.spotify.redirect');

        return regularResponse(['url' => $url]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        event(new SpotifyAccountLinked($request->code, $user));

        return response()->json(['data' => ['success' => true]]);
    }

    public function refreshSpotify()
    {
        $user = auth()->user();
        $user_spotify = $user->spotify;
        if (!$user_spotify || !$user_spotify->access_token) {
            return ['data' => ['success' => false]];
        }
        Artisan::call('spotify:refresh-user', [
            '--user' => $user->id,
        ]);

        return ['data' => ['success' => true]];
    }

    public function disconnectSpotify()
    {
        $user = auth('api')->user();
        $user_spotify = $user->spotify;
        foreach ($user_spotify->playlists as $playlist) {
            $playlist->tracks()->delete();
        }
        $user_spotify->playlists()->delete();
        $user_spotify->delete();
        $user->curator->playlists()->delete();
        return regularResponse([]);
    }
}
