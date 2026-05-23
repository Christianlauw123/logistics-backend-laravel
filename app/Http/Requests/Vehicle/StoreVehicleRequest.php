<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'name'          => ['nullable', 'string', 'min:1'],
            'plate_number'  => ['required', 'string', 'min:1', "unique:vehicles,plate_number"],
            'type'          => ['nullable', 'string', 'min:1'],
            'capacity'      => ['nullable', 'numeric', 'min:0.1'],
            'is_active'     => ['nullable','boolean'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
