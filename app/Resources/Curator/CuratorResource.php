<?php

namespace App\Http\Resources\Curator;

use Illuminate\Http\Resources\Json\JsonResource;

class CuratorResource extends JsonResource
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
            'external_user_id' => $this->external_user_id,
            'user_spotify_id' => $this->user_spotify_id,
            'price' => $this->price,
        ];
    }
}
