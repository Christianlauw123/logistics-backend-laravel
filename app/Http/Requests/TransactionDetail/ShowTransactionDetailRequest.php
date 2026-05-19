<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class ShowTransactionDetailRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'transaction_details'  => ['required', 'uuid', 'exists:transaction_details,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction_detail' => $this->route('transaction_detail'),
        ]);
    }
}
