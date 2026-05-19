<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionDetailStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['SUBMITTED', 'APPROVED', 'DONE', 'CANCELLED', 'REJECTED'])],
        ];
    }
}
