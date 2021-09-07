<?php

namespace App\Http\Controllers\Api\Curator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\CuratorPlaylist;
use App\Models\FeaturedPlaylistCalendar;
use KnotAShell\Orders\App\Http\Resources\PaymentResource;
use KnotAShell\Orders\Facades\Payment;
use KnotAShell\Orders\Models\Customer;
use KnotAShell\Orders\Models\Order;
use KnotAShell\Orders\Models\ProductType;

class CuratorFeaturedPlaylistCalendarController extends Controller
{
    public function index()
    {
        $featured_playlists = FeaturedPlaylistCalendar::paid()->where('date', '>=', now()->toDateString())->get()->pluck('date')->toArray();
        $featured_playlists = array_map(function ($item) {
            return date('Y-m-d', strtotime($item));
        }, $featured_playlists);
        return regularResponse(['dates' => $featured_playlists]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $curator_playlist = CuratorPlaylist::where('spotify_playlist_id', $request->id)->firstOrFail();

        $featured_playlist = FeaturedPlaylistCalendar::create([
            'curator_playlist_id' => $curator_playlist->id,
            'date' => $request->date,
        ]);

        $payment = Payment::setAmount(10)
                ->setFee(0)
                ->setUser($user)
                ->setProcessor($request->query('payment', 'stripe'))
                ->setReturnURL(config('app.url'))
                ->setCancelURL(config('app.url'))
                ->create();

        $customer = Customer::customerFromUser($user);
        $curator_product_type = ProductType::where('type', 'feature-playlist')->first();
        $order = Order::createOrder(
            $payment,
            $customer,
            $user,
            Order::STATUS_PENDING,
            $payment->amount,
            $curator_product_type,
            null,
        );

        $payment->paymentables()->create(['paymentable_id' => $featured_playlist->id, 'paymentable_type' => $featured_playlist->getMorphClass()]);
        $featured_playlist->order_id = $order->id;
        $featured_playlist->save();
        $order->orderables()->create(['orderable_id' => $featured_playlist->id, 'orderable_type' => $featured_playlist->getMorphClass()]);

        return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }
}
