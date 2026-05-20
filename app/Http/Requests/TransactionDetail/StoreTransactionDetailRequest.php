<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'numeric', 'min:0.1'],
            'note'              => ['nullable', 'string'],
            'purpose'           => ['nullable', 'string'],
            'transaction_id'    => ['required', 'uuid', 'exists:transactions,id'],
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
