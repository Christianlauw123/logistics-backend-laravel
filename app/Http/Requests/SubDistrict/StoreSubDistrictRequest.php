<?php

namespace App\Http\Requests\SubDistrict;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }
    public function rules(): array
    {
        return [
            'name'      => [
                'required',
                'string',
                'min:1',
                Rule::unique('sub_districts', 'name')->where(fn ($query) => $query->where('district_id', $this->districtId))->whereNull('deleted_at')
            ],
            'district_id'   => ['sometimes', 'uuid', 'exists:districts,id'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
