<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'do_number'             => $this->do_number,
            'status'                => $this->status,
            'dest_address'          => $this->dest_address,
            'do_date'               => $this->do_date,
            'do_actual_date'        => $this->do_actual_date,
            'created_at'            => $this->created_at?->toDateTimeString(),
            'vehicle_plate'         => $this->vehicle_plate,
            'vehicle_type'          => $this->vehicle_type,
            'vehicle_capacity'      => $this->vehicle_capacity,
            'transaction_capacity'  => $this->transaction_capacity,
            'transaction_items'     => $this->transaction_items,
            'bank_account_num'      => $this->bank_account_num,
            'customer_name'         => $this->customer_name,

            // Conditional: only load if relation is already loaded
            // Prevents N+1 — never loads relation just for the resource
            'user' => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'details' => $this->whenLoaded('details', fn () =>
                $this->details->where('deleted_at',null)->map(fn ($d) => [
                    'id'      => $d->id,
                    'purpose' => $d->purpose,
                    'amount'  => $d->amount,
                    'note'    => $d->note,
                    'status'  => $d->status,
                ])
            ),
            'attachments' => $this->whenLoaded('attachments', fn () =>
                $this->attachments->where('deleted_at',null)->map(fn ($a) => [
                    'id'                   => $a->id,
                    'file_url'             => $a->file_url,
                    'extracted_do_number'  => $a->extracted_do_number,
                    'is_verified'          => $a->is_verified,
                ])
            ),
        ];
    }
}
