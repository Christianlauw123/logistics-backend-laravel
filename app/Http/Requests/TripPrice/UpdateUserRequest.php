<?php

namespace App\Http\Requests\TripPrice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'                => ['sometimes', 'integer', 'exists:customers,id'],
            'origin_sub_district_id'      => ['sometimes', 'integer', 'exists:sub_districts,id'],
            'dest_sub_district_id' => [
                'sometimes',
                'integer',
                'exists:sub_districts,id',
                'different:origin_sub_district_id', // origin and destination cannot be the same
            ],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
