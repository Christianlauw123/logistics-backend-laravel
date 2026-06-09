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

        ];
    }
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama pelanggan ini sudah ada',
            'name.required' => 'Nama pelanggan harus diisi',
            'name.max' => 'Nama pelanggan maksimal 100 karakter',
        ];
    }
}
