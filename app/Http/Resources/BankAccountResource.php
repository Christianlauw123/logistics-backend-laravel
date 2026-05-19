<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'bank_name'                 => $this->bank_name,
            'account_identifier_number' => $this->account_identifier_number,
            'account_number'            => $this->account_number,
            'created_at'                => $this->created_at->toDateTimeString(),
            'updated_at'                => $this->updated_at->toDateTimeString(),
            'deleted_at'                => $this->deleted_at->toDateTimeString(),
        ];
    }
}
