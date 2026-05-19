<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubDistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'created_at'    => $this->created_at->toDateTimeString(),
            'updated_at'    => $this->updated_at->toDateTimeString(),
            'deleted_at'    => $this->deleted_at->toDateTimeString(),

            'district'      => $this->whenLoaded('district', fn () => [
                'id'        => $this->district->id,
                'name'      => $this->district->name,

                'city'      => $this->district->whenLoaded('city', fn () => [
                    'id'        => $this->city->id,
                    'name'      => $this->city->name,
                ]),
            ]),
        ];
    }
}
