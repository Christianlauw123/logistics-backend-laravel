<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ShowCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer'  => ['required', 'uuid', 'exists:customers,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer' => $this->route('customer'),
        ]);
    }
}
