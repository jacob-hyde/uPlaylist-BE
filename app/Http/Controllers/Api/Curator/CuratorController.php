<?php

namespace App\Http\Controllers\Api\Curator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\CuratorRequest;
use App\Http\Resources\Curator\CuratorResource;
use App\Models\ApiClient;
use App\Models\Curator;
use App\Models\User;

class CuratorController extends Controller
{
    public function index()
    {
        $curators = Curator::where('api_client_id', auth()->user()->id)->get();
        return CuratorResource::collection($curators)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function isCurator(Request $request)
    {
        $external_user = $request->header('X-EXTERNAL-USER') ? $request->header('X-EXTERNAL-USER') : $request->query('external_user');
        if (!$external_user) {
            return regularResponse(['is_curator' => false]);
        }
        if ($curator = Curator::where('external_user_id', $external_user)->where('api_client_id', auth()->user()->id)->first()) {
            return regularResponse(['is_curator' => true, 'playlist_count' => $curator->playlists->count(), 'price' => $curator->price, 'is_subscribed' => $curator->user->subscribed('curator')]);
        }
        return regularResponse(['is_curator' => false]);
    }


    public function getCuratorId(Request $request)
    {
        $external_user = $request->header('X-EXTERNAL-USER') ? $request->header('X-EXTERNAL-USER') : $request->query('external_user');
        if (!$external_user) {
            return regularResponse(['curator_id' => false]);
        }
        if ($curator = Curator::where('external_user_id', $external_user)->where('api_client_id', auth()->user()->id)->first()) {
            return regularResponse(['curator_id' => $curator->id]);
        }
        return regularResponse(['curator_id' => false]);
    }

    public function show(Curator $curator)
    {
        if ($curator->api_client_id !== auth()->user()->id) {
            return regularResponse([], false, null, Response::HTTP_FORBIDDEN);
        }
        return (new CuratorResource($curator))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(CuratorRequest $request)
    {
        $api_client = auth()->user();
        $data = $request->only(['external_user_id', 'email', 'first_name', 'last_name']);
        $data['api_client_id'] = $api_client->id;
        $user = User::withTrashed()->updateOrCreate(['api_client_id' => $api_client->id, 'external_user_id' => $data['external_user_id']], $data);
        if ($user->trashed()) {
            $user->restore();
        }
        $data = $request->only(['external_user_id', 'spotify_id']);
        $data['api_client_id'] = $api_client->id;
        $data['user_id'] = $user->id;
        $data['verified'] = 1;
        $curator = Curator::withTrashed()->updateOrCreate(['api_client_id' => $api_client->id, 'user_id' => $user->id], $data);
        if ($curator->trashed()) {
            $curator->playlists()->withTrashed()->get()->each(function ($playlist) use ($curator) {
                if ($playlist->deleted_at && $playlist->deleted_at->format('Y-m-d H:i') === $curator->deleted_at->format('Y-m-d H:i')) {
                    $playlist->restore();
                }
            });
            $curator->restore();
        }
        $curator->refresh();
        return (new CuratorResource($curator))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(Request $request)
    {
        $api_client = auth()->user();
        $external_user_id = $request->header('X-EXTERNAL-USER') ? $request->header('X-EXTERNAL-USER') : $request->query('external_user');
        $api_clients = ApiClient::with(['webhooks'])->whereHas('webhooks')->get();

        if ($curator = Curator::where('api_client_id', $api_client->id)->where('external_user_id', $external_user_id)->first()) {
            $playlist_ids = $curator->playlists->pluck('id');
            if (count($playlist_ids) !== 0) {
                foreach ($api_clients as $api_client) {
                    $api_client->sendWebhookEvent('playlists-deleted', ['playlist_ids' => $playlist_ids]);
                }
            }
            $curator->delete();
        }
    }
}
