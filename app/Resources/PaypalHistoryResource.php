<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaypalHistoryResource extends JsonResource
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
            'id' => $this->id,
            'status' => $this->status,
            'amount' => convertCentsToDollars($this->amount),
            'created_at' => $this->created_at->toIso8601String(),
            'paypal_email' => $this->paypal_email
        ];
    }
}
