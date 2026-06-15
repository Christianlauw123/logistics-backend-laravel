<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $driverId = $this->route('driver');

        return [
            'name'     => ['required', 'string', 'max:100', "unique:drivers,name,{$driverId}"]
        ];
    }
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama driver ini sudah ada',
            'name.required' => 'Nama driver harus diisi',
            'name.max' => 'Nama driver maksimal 100 karakter'
        ];
    }
}
