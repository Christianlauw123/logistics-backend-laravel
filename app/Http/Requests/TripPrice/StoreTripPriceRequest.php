<?php

namespace App\Http\Requests\TripPrice;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'                => ['required', 'uuid', 'exists:customers,id'],
            'origin_sub_district_id'      => ['required', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id' => [
                'required',
                'uuid',
                'exists:sub_districts,id',
                'different:origin_sub_district_id', // origin and destination cannot be the same
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
