<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;

class ShowDistrictRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'district'  => ['required', 'uuid', 'exists:districts,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'district' => $this->route('district'),
        ]);

        dd($this->route);
    }
}
