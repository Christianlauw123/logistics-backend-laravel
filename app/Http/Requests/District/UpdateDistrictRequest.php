<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        $districtId = $this->route('district');
        return [
            'name'      => ['required', 'string', 'min:1', "unique:districts,name,{$districtId}"],
            // 'city_id'   => ['sometimes', 'uuid', 'exists:cities,id'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
