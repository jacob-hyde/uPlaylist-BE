<?php

namespace App\Http\Resources\Curator;

use Illuminate\Http\Resources\Json\JsonResource;

class CuratorStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'orders_completed' => $this->resource->get('orders_completed'),
            'orders_pending' => $this->resource->get('orders_pending'),
            'total_orders' => $this->resource->get('total_orders'),
            'payout_amount' => $this->resource->get('payout_amount'),
        ];
    }
}
