<?php

namespace App\Services;

use App\Models\UserSpotify;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    public $continue_on_request_error = true;
    public $user_spotify;
    private $client_id;
    private $client_secret;

    public function __construct(UserSpotify $user_spotify = null)
    {
        $this->client_id = config('services.spotify.client_id');
        $this->client_secret = config('services.spotify.client_secret');
        $this->user_spotify = $user_spotify;
    }

    /**
     * Get a sever side API client.
     *
     * @return Client
     * @throws \Exception
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getApiClient(): Client
    {
        $token = $this->_getClientAccessToken();

        return $this->_getApiClientWithAccessToken($token);
    }

    /**
     * Get a user API client.
     *
     * @return Client
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserApiClient(): Client
    {
        if (! $this->user_spotify->access_token) {
            throw new Exception('User access not granted to spotify user with id: '.$this->user_spotify->user_id);
        }
        //try to refresh token if it is expired
        if (now() > $this->user_spotify->token_expires && ! $this->_refreshToken()) {
            throw new Exception('Unable to refresh token for spotify user with id: '.$this->user_spotify->user_id);
        }

        return $this->_getApiClientWithAccessToken($this->user_spotify->access_token);
    }

    public function getArtistPopularity(): ?object
    {
        $client = $this->getApiClient();
        $spotify_artist_id = $this->user_spotify->spotify_artist_id;

        return $this->_doRequest(
            $client,
            "artists/{$spotify_artist_id}"
        );
    }

    /**
     * Verify a spotify artist id.
     *
     * @param  string  $spotify_artist_id
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function verifyArtistId(string $spotify_artist_id): bool
    {
        $client = $this->getApiClient();

        return $this->_doRequest($client, 'artists/'.$spotify_artist_id) === null ? false : true;
    }

    /**
     * Fetches the artists top tracks in the US.
     *
     * @return object - The results
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getArtistTopTracks(): ?object
    {
        $client = $this->getApiClient();
        $spotify_artist_id = $this->user_spotify->spotify_artist_id;

        return $this->_doRequest($client, 'artists/'.$spotify_artist_id.'/top-tracks?country=US');
    }

    /**
     * Fetch the current user's basic information.
     *
     * @return object - The results
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getUserInfo(): object
    {
        $client = $this->getUserApiClient();

        return $this->_doRequest($client, 'me');
    }

    /**
     * Fetch the current user's playlists.
     *
     * @return object - The results
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getUserPlaylists(): object
    {
        $client = $this->getUserApiClient();

        $offset = 0;
        $playlists = $this->_doRequest($client, 'me/playlists?limit=50&offset=' . $offset);

        if ($playlists && $playlists->total <= 50) {
            return $playlists;
        }

        $items = $playlists ? $playlists->items : [];
        while ($offset < $playlists->total) {
            $offset += 50;
            $playlists = $this->_doRequest($client, 'me/playlists?limit=50&offset=' . $offset);
            $items = array_merge($items, $playlists->items);
        }
        return (object)['items' => $items];

    }

    public function verifyUser(string $spotify_user_id): bool
    {
        $client = $this->getApiClient();

        return $this->_doRequest($client, 'users/'.$spotify_user_id) === null ? false : true;
    }

    public function getUserPlaylistDetails(string $playlist_id): ?object
    {
        $client = $this->getUserApiClient();

        return $this->_doRequest($client, 'playlists/'.$playlist_id);
    }


    public function verifyPlaylist(string $playlist_id): bool
    {
        $client = $this->getApiClient();

        return $this->_doRequest($client, 'playlists/'.$playlist_id) === null ? false : true;
    }


    public function verifyTrack(string $track_id): bool
    {
        $client = $this->getApiClient();

        return $this->_doRequest($client, 'tracks/'.$track_id) === null ? false : true;
    }

    public function followPlaylist(string $playlist_id): bool
    {
        $client = $this->getUserApiClient();

        return $this->_doRequest($client, 'playlists/' . $playlist_id . '/followers', 'PUT') === null ? false : true;
    }

    /**
     * Returns the user access credentials.
     *
     * @param  string  $code
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserAccessCredentialsFromCode(string $code): array
    {
        $accounts_client = $this->_getAccountsClient();

        $spotify_data = Cache::get($this->user_spotify->user->id . 'spotify_connect');
        // TODO redirect_url is sometimes null - ARD-1819
        $redirect_uri = $spotify_data['redirect_url'];

        $response = $this->_doRequest($accounts_client, 'token', 'POST', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'code_verifier' => $spotify_data['code_verifier'],
        ]);

        Cache::forget($this->user_spotify->user->id . 'spotify_connect');

        return ['access_token' => optional($response)->access_token,
            'token_expires' => now()->addSeconds(optional($response)->expires_in),
            'refresh_token' => optional($response)->refresh_token, ];
    }

    /**
     * Does a HTTP request for a client and handles errors correctly.
     *
     * @param  Client  $client  - The Guzzle client
     * @param  string  $uri     - The URI to hit
     * @param  string  $method  - The HTTP method
     * @param  array   $params  - Any body params
     *
     * @return object - The response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    private function _doRequest(Client $client, string $uri, string $method = 'GET', array $params = []): ?object
    {
        $options = [];
        if (! empty($params)) {
            $options['form_params'] = $params;
        }

        try {
            $response = $client->request($method, $uri, $options);
        } catch (RequestException | ClientException $e) {
            Log::error($e->getMessage());

            if (! $this->continue_on_request_error) {
                throw new Exception($e->getMessage(), $e->getCode());
            } else {
                return null;
            }
        }

        $status_code = $response->getStatusCode();
        if ($status_code !== 200 && $status_code !== 201) {
            $error_msg = strtr('Spotify request failed. URI: {uri} Method: {method} with response: {response}', [
                '{uri}' => $uri,
                '{method}' => $method,
                '{response}' => $response->getBody()->getContents(),
            ]);
            if (! $this->continue_on_request_error) {
                throw new Exception($error_msg);
            } else {
                Log::error($error_msg);
                return null;
            }
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Returns the "client credential" access token.
     *
     * @return string - The access token
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _getClientAccessToken(): ?string
    {
        $accounts_client = $this->_getAccountsClient();
        $response = $this->_doRequest($accounts_client, 'token', 'POST', ['grant_type' => 'client_credentials']);

        return optional($response)->access_token;
    }

    /**
     * Trys to refresh a token.
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _refreshToken(): bool
    {
        $accounts_client = $this->_getAccountsClient();
        try {
            $response = $this->_doRequest($accounts_client, 'token', 'POST', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->user_spotify->refresh_token,
            ]);
            $data = ['access_token' => optional($response)->access_token, 'token_expires' => now()->addSeconds($response->expires_in)];
            if (isset($response->refresh_token)) {
                $data['refresh_token'] = optional($response)->refresh_token;
            }
            $this->user_spotify->update($data);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns a Client with a access token.
     *
     * @param string $token
     * @return void
     */
    private function _getApiClientWithAccessToken(string $token): Client
    {
        return new Client([
            'base_uri' => 'https://api.spotify.com/v1/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Creates an account client for authorization with spotify.
     *
     * @return Client
     */
    private function _getAccountsClient(): Client
    {
        return new Client([
            'base_uri' => 'https://accounts.spotify.com/api/',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
                'Accept' => 'application/json',
            ],
        ]);
    }
}
