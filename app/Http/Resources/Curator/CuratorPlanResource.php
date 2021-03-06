<?php

namespace App\Http\Resources\Curator;

use Illuminate\Http\Resources\Json\JsonResource;

class CuratorPlanResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'details' => $this->details
        ];
    }
}
