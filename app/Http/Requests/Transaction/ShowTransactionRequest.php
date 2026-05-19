<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class ShowTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'transaction'  => ['required', 'uuid', 'exists:transactions,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction' => $this->route('transaction'),
        ]);
    }
}
