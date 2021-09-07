<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpotifyPlaylistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'playlist_id' => $this->playlist_id,
            'followers' => $this->followers,
            'img_url' => $this->img_url,
            'url' => $this->url,
            'track_count' => $this->tracks_count,
            'curator_playlist' => $this->curator_playlist_count > 0,
            'price' => $this->curator_playlist ? convertCentsToDollars($this->curator_playlist->amount) : null,
        ];
    }
}
