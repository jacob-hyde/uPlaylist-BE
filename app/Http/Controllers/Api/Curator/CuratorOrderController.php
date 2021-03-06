<?php

namespace App\Http\Controllers\Api\Curator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\CuratorOrderCreateRequest;
use App\Http\Requests\CuratorOrderUpdateRequest;
use App\Http\Requests\OrderRegisterRequest;
use App\Http\Resources\Curator\CuratorOrderResource;
use App\Http\Resources\OrderResource;
use App\Mail\CuratorOrderUpdatedMail;
use App\Models\CuratorOrder;
use App\Models\User;
use App\Models\UserTrack;
use Illuminate\Support\Facades\Mail;
use KnotAShell\Orders\App\Http\Resources\PaymentResource;
use KnotAShell\Orders\Facades\Payment;
use KnotAShell\Orders\Models\Cart;
use KnotAShell\Orders\Models\Customer;
use KnotAShell\Orders\Models\Order;
use KnotAShell\Orders\Models\ProductType;

class CuratorOrderController extends Controller
{

    public function register(OrderRegisterRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return regularResponse($user->getOrderUserLoginData(), true, null, Response::HTTP_OK);
        }
        $data = $request->all();
        $user = User::create($data);
        return regularResponse($user->getOrderUserLoginData(), true, null, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/curator/order",
     *      operationId="getCuratorOrders",
     *      tags={"Order"},
     *      summary="Get list of curator orders",
     *      description="Returns list of curator orders",
     *      security={{"passport": {"*"}}},
     *      @OA\Parameter(
     *          name="curator",
     *          in="query",
     *          description="Curator ID or email",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="external_user",
     *          in="query",
     *          description="External User ID to find orders for a specific user",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="Email of user to find orders for a specific user",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Status of the curator order",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CuratorOrderResource")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            $api_client = auth('api-clients')->user();
            $corders_query = CuratorOrder::with(['user', 'order', 'playlist', 'playlist.genres', 'curator', 'user_track'])
                ->paid()
                ->select('curator_orders.*');
            $corders_query->where('curator_orders.api_client_id', $api_client->id);
        } else {
            $corders_query = CuratorOrder::with(['user', 'order', 'playlist', 'playlist.genres', 'curator', 'user_track'])
                ->paid()
                ->select('curator_orders.*')
                ->whereHas('curator', function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                });
        }

        if ($curator = $request->query('curator')) {
            if (is_numeric($curator)) {
                $corders_query->where('curator_id', $curator);
            } else {
                $corders_query->whereHas('curator', function ($q) use ($curator) {
                    return $q->where('email', $curator);
                });
            }
        }
        if ($external_user = $request->header('X-EXTERNAL-USER')) {
            $corders_query->whereHas('curator', function ($q) use ($external_user) {
                return $q->where('external_user_id', $external_user);
            });
        }
        if ($exteneral_user = $request->query('external_user')) {
            $corders_query->where('curator_orders.external_user_id', $exteneral_user);
        }
        if ($email = $request->query('email')) {
            $corders_query->whereHas('user', function ($q) use ($email) {
                return $q->where('email', $email);
            });
        }
        if ($status = $request->query('status')) {
            if ($status === 'pending') {
                $corders_query->where('curator_orders.status', $status);
            } else {
                $corders_query->where('curator_orders.status', '!=', 'pending');
            }
        }

        return CuratorOrderResource::collection($corders_query->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/curator/order",
     *      operationId="storeCuratorOrder",
     *      tags={"Order"},
     *      summary="Create new Curator Order",
     *      description="Returns order uuid",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CuratorOrderRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Order")
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
    public function store(CuratorOrderCreateRequest $request)
    {
        $seller = auth('api-clients')->user();
        $user = auth('api')->user();
        $user_track = UserTrack::where('uuid', $request->user_track_uuid)->firstOrFail();

        if (!$user) {
            $user_data = $request->only(['first_name', 'last_name', 'email']);
            $user_data['external_user_id'] = $request->external_user_id ? $request->external_user_id : $request->header('X-EXTERNAL-USER');
            $user_data['api_client_id'] = auth('api-clients')->user()->id;
            $user = User::updateOrCreate(['email' => $request->email], $user_data);
        }

        $curator_orders = CuratorOrder::createOrdersFromPlaylistIds($request->playlists, $user_track, $user);
        $amount = convertCentsToDollars(CuratorOrder::getCostFromPlaylistIds($request->playlists));
        $fee = convertCentsToDollars(array_reduce($curator_orders, function ($accum, $v) {
            $accum += $v->amount;
            return $accum;
        }, 0));

        if (auth('api')->user()) {
            $amount += ($amount * 0.3) + 0.5;
            $payment = Payment::setAmount($amount)
                ->setFee(0)
                ->setUser($user)
                ->setProcessor($request->query('payment', 'stripe'))
                ->setReturnURL(config('app.url') . CuratorOrder::RETURN_URL)
                ->setCancelURL(config('app.url') . CuratorOrder::CANCEL_URL)
                ->create();
        } else {
            $payment = Payment::setAmount($amount)
                ->setFee($fee)
                ->setUser($user)
                ->createFee();
        }

        $customer = Customer::customerFromUser($user);
        $curator_product_type = ProductType::where('type', 'curator')->first();
        $order = Order::createOrder(
            $payment,
            $customer,
            $user,
            Order::STATUS_PENDING,
            $payment->amount,
            $curator_product_type,
            $seller ? $seller->id : null,
            $seller ? $seller->id : null,
        );

        foreach ($curator_orders as $curator_order) {
            $payment->paymentables()->create(['paymentable_id' => $curator_order->id, 'paymentable_type' => $curator_order->getMorphClass()]);
            $curator_order->order_id = $order->id;
            $curator_order->save();
            $order->orderables()->create(['orderable_id' => $curator_order->id, 'orderable_type' => $curator_order->getMorphClass()]);
        }

        if (auth('api')->user()) {
            return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        }

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @OA\Get(
     *      path="/curator/order/{uuid}",
     *      operationId="getCuratorOrderByUUID",
     *      tags={"Order"},
     *      summary="Get Curator Order information",
     *      description="Returns curator order data",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="Curtor Order UUID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CuratorOrder")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show(CuratorOrder $corder)
    {
        $corder->load(['user', 'order', 'playlist', 'playlist.genres', 'curator', 'curator.user', 'user_track']);
        return (new CuratorOrderResource($corder))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(CuratorOrder $corder, CuratorOrderUpdateRequest $request)
    {
        $data = $request->only(['status', 'feedback']);
        if (isset($data['status'])) {
            $data['status_changed_at'] = now();
        }

        $corder->update($data);

        if ($corder->api_client) {
            $corder->api_client->sendWebhookEvent('order-updated', ['curator_order' => $corder->uuid]);
        }

        $corder->load(['user', 'order', 'playlist', 'playlist.genres', 'curator', 'curator.user', 'user_track']);

        Mail::to($corder->user->email)->queue(new CuratorOrderUpdatedMail($corder));

        return (new CuratorOrderResource($corder))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

}
