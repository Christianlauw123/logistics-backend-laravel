<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100', 'unique:drivers,name'],
            'is_active' => ['nullable','boolean'],

        ];
    }
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama driver ini sudah ada',
            'name.required' => 'Nama driver harus diisi',
            'name.max' => 'Nama driver maksimal 100 karakter',
        ];
    }
}
