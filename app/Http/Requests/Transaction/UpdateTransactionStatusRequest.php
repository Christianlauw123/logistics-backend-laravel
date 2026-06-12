<?php

namespace App\Http\Requests\Transaction;

use App\Enums\Transactions\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(TransactionStatus::cases(),'value'))],
        ];
    }


    public function messages(): array
    {
        return [
            'status.required' => 'Status harus ada',
            'status.in' => 'Status tidak valid',
        ];
    }
}
