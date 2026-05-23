<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'base_price'    => $this->base_price,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,

            'origin'      => $this->whenLoaded('originSubDistrict', fn ($d) => [
                'id'        => $this->originSubDistrict->id,
                'name'      => $this->originSubDistrict->name,
            ]),
            'destination'      => $this->whenLoaded('destinationSubDistrict', fn ($d) => [
                'id'        => $this->destinationSubDistrict->id,
                'name'      => $this->destinationSubDistrict->name,
            ]),

            'customer'      => $this->whenLoaded('customer', fn () => [
                'name'      => $this->customer->name,
            ]),
        ];
    }
}
