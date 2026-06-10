<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    protected $calculations;

    public function __construct($resource, array $calculations = [])
    {
        parent::__construct($resource);
        $this->calculations = $calculations;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'do_number'                 => $this->do_number,
            'status'                    => $this->status,
            'dest_address'              => $this->dest_address,
            'do_date'                   => $this->do_date,
            'do_actual_date'            => $this->do_actual_date,
            'created_at'                => $this->created_at,
            'vehicle_plate'             => $this->vehicle_plate,
            'vehicle_type'              => $this->vehicle_type,
            'vehicle_capacity'          => $this->vehicle_capacity,
            'transaction_capacity'      => $this->transaction_capacity,
            'transaction_items'         => $this->transaction_items,
            'bank_account_num'          => $this->bank_account_num,
            'customer_name'             => $this->customer_name,
            'driver_name'               => $this->driver_name,
            'trip_price_amount'         => $this->trip_price_amount,
            'note'                      => $this->note,
            'origin_district'           => $this->origin_district,
            'destination_district'      => $this->destination_district,
            'customer_id'               => $this->customer_id,
            'vehicle_id'                => $this->vehicle_id,
            'bank_account_id'           => $this->bank_account_id,
            'origin_sub_district_id'    => $this->origin_sub_district_id,
            'dest_sub_district_id'      => $this->dest_sub_district_id,
            'driver_id'                 => $this->driver_id,
            'current_total'             => $this->calculations['current_total'] ?? 0, // Custom Fields
            'current_total_approved'    => $this->calculations['current_total_approved'] ?? 0, // Custom Fields
            // Conditional: only load if relation is already loaded
            // Prevents N+1 — never loads relation just for the resource
            'user' => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'details' => $this->whenLoaded('transactionDetails', fn () =>
                $this->transactionDetails->where('deleted_at',null)->map(fn ($d) => [
                    'id'      => $d->id,
                    'purpose' => $d->purpose,
                    'amount'  => $d->amount,
                    'note'    => $d->note,
                    'status'  => $d->status,
                    // Attachment Transaction Detail
                    'attachments' => $d->relationLoaded('attachments', fn () =>
                        $d->attachments->where('deleted_at',null)->map(fn ($a) => [
                            'id'                   => $a->id,
                            'file_url'             => $a->file_url,
                            'extracted_do_number'  => $a->extracted_do_number,
                            'is_verified'          => $a->is_verified,
                        ])
                    ),
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
