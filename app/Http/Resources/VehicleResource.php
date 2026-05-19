<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->base_price,
            'plate_number'  => $this->plate_number,
            'type'          => $this->type,
            'capacity'      => $this->capacity,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
            'deleted_at'    => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
