<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionDetailExternalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'numeric', 'min:0.1'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah harus bernilai positif'
        ];
    }
}
