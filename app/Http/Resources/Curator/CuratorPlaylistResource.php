<?php

namespace App\Http\Resources\Curator;

use App\Http\Resources\GenreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CuratorPlaylistResource extends JsonResource
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
            'username' => $this->username,
            'is_subscribed' => $this->curator->user->subscribed('curator'),
            'spotify_id' => $this->curator->spotify_id,
            'name' => $this->name,
            'img_url' => $this->img_url,
            'genres' => GenreResource::collection($this->genres),
            'followers' => $this->followers,
            'url' => $this->url,
            'placement'=> $this->placement,
            'price' => $this->amount ? $this->amount : $this->curator->price,
        ];
    }
}
