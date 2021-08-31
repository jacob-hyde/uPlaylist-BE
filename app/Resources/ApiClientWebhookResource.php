<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiClientWebhookResource extends JsonResource
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
            'uuid' => $this->uuid,
            'url' => $this->url,
            'events' => $this->events,
            'secret' => $this->secret,
        ];
    }
}
