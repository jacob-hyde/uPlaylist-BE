<?php

namespace App\Http\Controllers\Api\Curator;

use App\Http\Controllers\Controller;
use App\Http\Resources\Curator\CuratorStatsResource;
use App\Models\Curator;
use App\Models\CuratorOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CuratorStatsController extends Controller
{

    public function index(Request $request)
    {
        $api_client = auth()->user();
        $external_user_id = $request->header('X-EXTERNAL-USER');
        $data = ['orders_completed' => 0, 'orders_pending' => 0, 'total_orders' => 0, 'payout_amount' => 0];
        $curator = Curator::where('external_user_id', $external_user_id)->first();
        if ($curator) {
            $orders = CuratorOrder::paid()
                ->where('curator_orders.api_client_id', $api_client->id)
                ->where('curator_orders.curator_id', $curator->id)
                ->get(['curator_orders.*']);
            $data['orders_completed'] = $orders->where('status', '!=', 'pending')->count();
            $data['orders_pending'] = $orders->where('status', 'pending')->count();
            $data['total_orders'] = $orders->count();
            $data['payout_amount'] = convertCentsToDollars($curator->payout_amount);
        }

        return (new CuratorStatsResource(collect($data)))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
