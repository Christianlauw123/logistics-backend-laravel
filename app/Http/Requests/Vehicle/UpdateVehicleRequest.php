<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'min:1'],
            'plate_number'  => ['sometimes', 'string', 'min:1'],
            'type'          => ['sometimes', 'string', 'min:1'],
            'capacity'      => ['sometimes', 'numeric', 'min:0.1'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
