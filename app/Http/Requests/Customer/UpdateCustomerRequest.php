<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'min:1'],
            'phone'     => ['sometimes', 'string', 'min:1'],
            'address'   => ['sometimes', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
