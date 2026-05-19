<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'name'     => ['sometimes', 'string', 'max:100', "unique:customers,name,{$customerId}"],
            'phone' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
