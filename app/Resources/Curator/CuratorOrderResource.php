<?php

namespace App\Http\Resources\Curator;

use App\Http\Resources\UserTrackResource;
use App\Models\Curator\CuratorPlaylist;
use App\Models\UserTrack;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CuratorOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }
        return [
            'uuid' => $this->uuid,
            'order_uuid' => $this->order->uuid,
            'curator_id' => $this->curator_id,
            'amount' => $this->amount,
            'playlist_price' => $this->playlist_price,
            'playlist' => new CuratorPlaylistResource($this->whenLoaded('playlist')),
            'user_track' => new UserTrackResource($this->whenLoaded('user_track')),
            'user_track_uuid' => $this->user_track->uuid,
            'status' => $this->status,
            'status_changed_at' => $this->status_changed_at ? $this->status_changed_at->toiso8601string() : '',
            'feedback' => $this->feedback,
            'added_to_playlist' => $this->added_to_playlist,
            'user_uuid' => $this->user->uuid,
            'created_at' => $this->created_at->toiso8601string(),
        ];
    }
}
