<?php

namespace App\Http\Controllers\Api\Curator;

use App\Http\Controllers\Controller;
use App\Http\Resources\Curator\CuratorPlaylistInternalResource;
use App\Http\Resources\Curator\CuratorPlaylistResource;
use App\Models\ApiClient;
use App\Models\Curator;
use App\Models\CuratorPlaylist;
use App\Models\FeaturedPlaylistCalendar;
use App\Models\SpotifyPlaylist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CuratorPlaylistController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $playlist = CuratorPlaylist::where('spotify_playlist_id', $request->id)->first();
        if ($playlist) {
            $playlist->amount = convertDollarsToCents($request->price);
        } else {
            $playlist = new CuratorPlaylist();
            $playlist->curator_id = $user->curator->id;
            $playlist->spotify_playlist_id = $request->id;
            $playlist->amount = convertDollarsToCents($request->price);
        }
        $playlist->updateFromSpotify();
        $playlist->save();
        $playlist->genres()->sync($request->genres);

        $api_clients = ApiClient::with(['webhooks'])->whereHas('webhooks')->get();
        foreach ($api_clients as $api_client) {
            $api_client->sendWebhookEvent('playlists-updated', ['playlist_ids' => [$playlist->id]]);
        }

        return regularResponse(['message' => 'Playlist saved successfully']);
    }

    public function featured()
    {
        $featured_playlist = FeaturedPlaylistCalendar::paid()->where('date', now()->toDateString())->first();
        if (!$featured_playlist) {
            $featured_playlist = CuratorPlaylist::inRandomOrder()->first();
        } else {
            $featured_playlist = $featured_playlist->playlist;
        }
        return (new CuratorPlaylistResource($featured_playlist))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(SpotifyPlaylist $playlist)
    {
        if ($playlist->curator_playlist) {
            $playlist->curator_playlist->delete();
        }

        $api_clients = ApiClient::with(['webhooks'])->whereHas('webhooks')->get();
        foreach ($api_clients as $api_client) {
            $api_client->sendWebhookEvent('playlists-deleted', ['playlist_ids' => [$playlist->id]]);
        }

        return regularResponse();
    }

    public function show(CuratorPlaylist $playlist)
    {
        $playlist->load(['spotify_playlist.tracks']);
        return (new CuratorPlaylistResource($playlist))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
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
        if ($search = $request->query('search')) {
            $playlists_query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }
        if ($request->has('exclude_playlist_ids')) {
            $playlists_query->whereNotIn('id', array_filter($request->exclude_playlist_ids));
        }
        if ($curator_id = $request->has('exclude_curator_id')) {
            $playlists_query->where('curator_id', '!=', $curator_id);
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

    public function search(Request $request)
    {
        $playlists = CuratorPlaylist::search($request->search)->paginate(10);
        return CuratorPlaylistResource::collection($playlists)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

}
