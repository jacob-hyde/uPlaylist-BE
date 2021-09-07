<?php

namespace App\Http\Controllers\Api\Curator;

use App\Http\Controllers\Controller;
use App\Http\Resources\Curator\CuratorPlaylistInternalResource;
use App\Http\Resources\Curator\CuratorPlaylistResource;
use App\Models\ApiClient;
use App\Models\Curator;
use App\Models\CuratorPlaylist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CuratorPlaylistController extends Controller
{

    public function index(Request $request)
    {
        $external_user = $request->header('X-EXTERNAL-USER');
        $curator_playlists = CuratorPlaylist::whereHas('curator', function ($q) use ($external_user) {
            return $q->where('api_client_id', auth()->user()->id)->where('external_user_id', $external_user);
        })->get();

        return CuratorPlaylistInternalResource::collection($curator_playlists)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $external_user = $request->header('X-EXTERNAL-USER');
        $curator = Curator::where('api_client_id', auth()->user()->id)->where('external_user_id', $external_user)->first();
        $current_curator_playlist_ids = CuratorPlaylist::whereHas('curator', function ($q) use ($external_user) {
            return $q->where('api_client_id', auth()->user()->id)->where('external_user_id', $external_user);
        })->get()->pluck('id')->toArray();
        $synced_curator_playlist_ids = [];
        $webhook_events = [
            'playlists-updated' => [],
            'playlists-deleted' => [],
        ];
        foreach ($request->selected as $playlist) {
            $price = $playlist['price'];
            if ($price != null && $price > $curator->price) {
                $price = $curator->price;
            }
            $curator_playlist = CuratorPlaylist::withTrashed()
                ->where('curator_id', $curator->id)
                ->where('spotify_playlist_id', $playlist['spotify_playlist_id'])->first();
            if ($curator_playlist) {
                $curator_playlist->update(['amount' => convertDollarsToCents($price)]);
                if ($curator_playlist->trashed()) {
                    $curator_playlist->restore();
                }
                $synced_curator_playlist_ids[] = $curator_playlist->id;
            } else {
                $curator_playlist = CuratorPlaylist::create([
                                                            'spotify_playlist_id' => $playlist['spotify_playlist_id'],
                                                            'curator_id' => $curator->id,
                                                            'name' => $playlist['name'],
                                                            'username' => $playlist['username'],
                                                            'followers' => $playlist['followers'],
                                                            'amount' => convertDollarsToCents($price),
                                                            'url' => $playlist['url'],
                                                            'img_url' => $playlist['image_url'],
                                                        ]);
            }
            $curator_playlist->genres()->sync(collect($playlist['genres'])->pluck('id'));
            $webhook_events['playlists-updated'][] = $curator_playlist->id;
        }
        CuratorPlaylist::whereIn('id', array_diff($current_curator_playlist_ids, $synced_curator_playlist_ids))->delete();

        $webhook_events['playlists-deleted'] = array_diff($current_curator_playlist_ids, $synced_curator_playlist_ids);
        $api_clients = ApiClient::with(['webhooks'])->whereHas('webhooks')->get();

        foreach ($webhook_events as $event => $ids) {
            if (count($ids) === 0) {
                continue;
            }
            foreach ($api_clients as $api_client) {
                $api_client->sendWebhookEvent($event, ['playlist_ids' => $ids]);
            }
        }

        $curator_playlists = CuratorPlaylist::whereHas('curator', function ($q) use ($external_user) {
            return $q->where('api_client_id', auth()->user()->id)->where('external_user_id', $external_user);
        })->get();

        return CuratorPlaylistInternalResource::collection($curator_playlists)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/curator/playlist/playlists",
     *      operationId="getPlaylistsList",
     *      tags={"Playlists"},
     *      summary="Get list of playlists",
     *      description="Returns list of playlists",
     *      security={{"passport": {"*"}}},
     *      @OA\Parameter(
     *          name="genres",
     *          in="query",
     *          description="Genre ID's comma seperated",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="ids",
     *          in="query",
     *          description="Playlist ID's to get",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Playlist name",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="username",
     *          in="query",
     *          description="Spotify username",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="followersMin",
     *          in="query",
     *          description="Min followers of a playlist (followersMax also required)",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="followersMax",
     *          in="query",
     *          description="Max followers of a playlist (followersMin also required)",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="priceMin",
     *          in="query",
     *          description="Min price of a playlist (priceMax also required)",
     *          @OA\Schema(
     *              type="number",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="priceMax",
     *          in="query",
     *          description="Max price of a playist (priceMin also required)",
     *          @OA\Schema(
     *              type="number",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CuratorPlaylistResource")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     *     )
     */
    public function playlists(Request $request)
    {
        $playlists_query = CuratorPlaylist::with(['curator', 'curator.user', 'genres'])
            ->curatorNotSuspended();
            // ->curatorVerified();
        if ($genres = $request->query('genres')) {
            $playlists_query->whereHas('genres',
                function ($q) use ($genres) {
                    return $q->whereIn('genre_id', [$genres]);
                }
            );
        }
        if ($ids = $request->query('ids')) {
            $playlists_query->whereIn('id', explode(',', $ids));
            return CuratorPlaylistResource::collection($playlists_query->get())
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }
        if ($name = $request->query('name')) {
            $playlists_query->where('name', 'like', '%'.$name.'%');
        }
        if ($name = $request->has('exclude_playlist_ids')) {
            $playlists_query->whereNotIn('id', array_filter($request->exclude_playlist_ids));
        }
        if ($curator_id = $request->has('exclude_curator_id')) {
            $playlists_query->where('curator_id', '!=', $curator_id);
        }
        if ($username = $request->query('username')) {
            $playlists_query->where('username', 'like', '%'.$username.'%');
        }
        if (($followers_min = $request->query('followersMin')) && ($followers_max = $request->query('followersMax'))) {
            $playlists_query->whereBetween('followers', [$followers_min, $followers_max]);
        }

        if (($price_min = $request->query('priceMin')) && ($price_max = $request->query('priceMax'))) {
            $playlists_query->whereBetween('amount', [convertDollarsToCents($price_min), convertDollarsToCents($price_max)]);
        }
        $seed = date('jnY');
        if ($request->has('for_upsell')) {
            $playlists = $playlists_query->inRandomOrder($seed)->paginate(25);
            if ($request->has('min_placement_rate')) {
                $playlists = $playlists->filter(
                    function ($item) use ($request) {
                        return $item->placement >= $request->min_placement_rate;
                    }
                );
            }
            return CuratorPlaylistResource::collection($playlists)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } else {
            return CuratorPlaylistResource::collection($playlists_query->inRandomOrder($seed)->paginate(25))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }
    }

    public function updatePlaylists(Request $request)
    {
        $playlists = json_decode($request->playlists);
        $updated_playlists = [];
        foreach ($playlists as $playlist) {
            $playlist = (array) $playlist;
            $curator_playlist = CuratorPlaylist::where('spotify_playlist_id', $playlist['playlist_id'])->first();
            if ($curator_playlist) {
                $curator_playlist->update([
                    'name' => $playlist['name'],
                    'followers' => $playlist['followers'] ? $playlist['followers'] : 0,
                    'img_url' => $playlist['image_url'],
                    'url' => $playlist['url'],
                    'username' => $playlist['spotify_id'],
                ]);
                $updated_playlists[] = $curator_playlist->id;
            }
            if ($curator_playlist && $curator_playlist->curator) {
                $curator_playlist->curator->update(['spotify_id' => $playlist['spotify_id']]);
            }
        }

        //Send webhook to APIClients with playlist updates
        $api_clients = ApiClient::with(['webhooks'])->whereHas('webhooks')->get();
        foreach ($api_clients as $api_client) {
            $api_client->sendWebhookEvent('playlists-updated', ['playlist_ids' => $updated_playlists]);
        }
    }
}
