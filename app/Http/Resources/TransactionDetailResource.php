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
            'amount'        => $this->amount,
            'note'          => $this->note,
            'purpose'       => $this->purpose,
            'status'        => $this->status,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
            'is_special_case' => $this->is_special_case,
            'transaction'   => $this->whenLoaded('transaction', fn () => [
                'id'        => $this->transaction->id,
            ]),
            'user'          => $this->whenLoaded('user', fn () => [
                'id'        => $this->user->id,
            ]),
        ];
    }
}
