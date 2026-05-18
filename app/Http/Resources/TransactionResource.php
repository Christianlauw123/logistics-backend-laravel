<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'do_number'      => $this->do_number,
            'status'         => $this->status,
            'dest_address'   => $this->dest_address,
            'do_date'        => $this->do_date?->toDateString(),
            'do_actual_date' => $this->do_actual_date?->toDateString(),
            'created_at'     => $this->created_at->toDateTimeString(),

            // Conditional: only load if relation is already loaded
            // Prevents N+1 — never loads relation just for the resource
            'customer'    => $this->whenLoaded('customer', fn () => [
                'id'   => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'id'           => $this->vehicle->id,
                'plate_number' => $this->vehicle->plate_number,
                'type'         => $this->vehicle->type,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'bank_account' => $this->whenLoaded('bankAccount', fn () => [
                'id'             => $this->bankAccount->id,
                'bank_name'      => $this->bankAccount->bank_name,
                'account_number' => $this->bankAccount->account_number,
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
