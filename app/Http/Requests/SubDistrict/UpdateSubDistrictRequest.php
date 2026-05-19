<?php

namespace App\Http\Requests\SubDistrict;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        $subDistrictId = $this->route('sub_district');
        return [
            'name'      => [
                'sometimes',
                'string',
                'min:1',
                Rule::unique('sub_districts', 'name')
                ->where(fn ($query) => $query->where('district_id', $this->district_id))->whereNull('deleted_at')
                ->ignore($subDistrictId, 'id')
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
