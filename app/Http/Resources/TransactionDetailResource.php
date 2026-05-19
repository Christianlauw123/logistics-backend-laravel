<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'amount'        => $this->name,
            'note'          => $this->note,
            'purpose'       => $this->purpose,
            'status'        => $this->status,
            'created_at'    => $this->created_at->toDateTimeString(),
            'updated_at'    => $this->updated_at->toDateTimeString(),
            'deleted_at'    => $this->deleted_at->toDateTimeString(),

            'transaction'   => $this->whenLoaded('transaction', fn () => [
                'id'        => $this->transaction->id,
            ]),
            'user'          => $this->whenLoaded('user', fn () => [
                'id'        => $this->user->id,
            ]),
        ];
    }
}
