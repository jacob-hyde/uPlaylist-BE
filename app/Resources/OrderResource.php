<?php
namespace App\Http\Resources;

use App\Http\Resources\Curator\CuratorOrderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
    * Transform the resource into an array.
    *
    * @param \Illuminate\Http\Request $request
    * @return array
    */
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
        ];
        if ($this->whenLoaded('orderables')) {
            $data['order_data'] = CuratorOrderResource::collection($this->orderables->pluck('orderable'));
        }
        return $data;
    }
}
