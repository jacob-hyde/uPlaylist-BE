<?php

namespace App\Http\Controllers\Api\Curator;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTrackCreateRequest;
use App\Http\Requests\UserTrackUpdateRequest;
use App\Http\Resources\UserTrackResource;
use App\Models\UserTrack;
use ArtistRepublik\AROrders\Models\Cart;
use ArtistRepublik\AROrders\Models\Intent;
use Illuminate\Http\Response;

class UserTrackController extends Controller
{
    /**
     * @OA\Post(
     *      path="/curator/user-track",
     *      operationId="storeUserTrack",
     *      tags={"User Track"},
     *      summary="Store new user track",
     *      description="Returns user track data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UserTrackCreateRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/UserTrack")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function store(UserTrackCreateRequest $request)
    {
        $data = $request->only(['name', 'url', 'genre_id', 'external_user_id']);
        $data['api_client_id'] = auth()->user()->id;
        if (!isset($data['external_user_id']) && $request->header('X-EXTERNAL-USER')) {
            $data['external_user_id'] = $request->header('X-EXTERNAL-USER');
        }
        $user_track = UserTrack::create($data);

        Intent::createIntent(auth()->user()->id, UserTrack::INTENT_STEP_CREATED, $user_track);

        return (new UserTrackResource($user_track))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/curator/user-track/{user_track_uuid}",
     *      operationId="updateUserTrack",
     *      tags={"User Track"},
     *      summary="Update user track",
     *      description="Returns user track data",
     *      @OA\Parameter(
     *          name="user_track_uuid",
     *          description="User track uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UserTrackUpdateRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/UserTrack")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function update(UserTrack $user_track, UserTrackUpdateRequest $request)
    {
        $user_track->update($request->only(['name', 'url', 'genre_id']));

        return (new UserTrackResource($user_track))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function cart(UserTrack $user_track)
    {
        $cart = Cart::createCart(auth()->user()->id, $user_track);
        return regularResponse(['key' => $cart->uuid], true, null, Response::HTTP_CREATED);
    }
}
