<?php

namespace App\Http\Requests\TripPrice;

use Illuminate\Foundation\Http\FormRequest;

class ShowTripPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'trip_price'  => ['required', 'uuid', 'exists:trip_prices,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'trip_price' => $this->route('trip_price'),
        ]);
    }
}
