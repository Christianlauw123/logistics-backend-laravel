<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'numeric', 'min:0.1'],
            'note'              => ['nullable', 'string'],
            'purpose'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah harus bernilai positif',
        ];
    }
}
