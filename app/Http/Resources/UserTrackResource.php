<?php

namespace App\Http\Resources;

use App\Http\Resources\Cart\CartResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTrackResource extends JsonResource
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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'url' => $this->url,
            'genre' => $this->genre->name,
        ];
    }
}
