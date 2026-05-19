<?php

namespace App\Http\Requests\SubDistrict;

use Illuminate\Foundation\Http\FormRequest;

class ShowSubDistrictRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_district'  => ['required', 'uuid', 'exists:sub_districts,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sub_district' => $this->route('sub_district'),
        ]);
    }
}
