<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100', 'unique:customers,name'],
            'phone' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:100'],

        ];
    }
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama pelanggan ini sudah ada',
            'name.required' => 'Nama pelanggan harus diisi',
            'name.max' => 'Nama pelanggan maksimal 100 karakter',
            'phone.max' => 'Nomor telepon maksimal 100 karakter',
            'address.max' => 'Alamat maksimal 100 karakter',
        ];
    }
}
