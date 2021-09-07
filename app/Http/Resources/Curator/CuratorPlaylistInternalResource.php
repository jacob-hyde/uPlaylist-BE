<?php

namespace App\Http\Resources\Curator;

use App\Http\Resources\GenreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CuratorPlaylistInternalResource extends JsonResource
{
    /**
    * Transform the resource into an array.
    *
    * @param \Illuminate\Http\Request $request
    * @return array
    */
    public function toArray($request)
    {
        return [
            'spotify_playlist_id' => $this->spotify_playlist_id,
            'price' => convertCentsToDollars($this->amount),
            'genres' => GenreResource::collection($this->genres),
        ];
    }
}
