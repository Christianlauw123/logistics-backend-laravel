<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'min:1'],
            'city_id'   => ['sometimes', 'uuid', 'exists:cities,id'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
