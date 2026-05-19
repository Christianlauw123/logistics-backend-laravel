<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'min:1'],
            'phone'     => ['nullable', 'string', 'min:1'],
            'address'   => ['nullable', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
