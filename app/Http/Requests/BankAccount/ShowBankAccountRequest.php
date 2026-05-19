<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;

class ShowBankAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'bank_account'  => ['required', 'uuid', 'exists:bank_accounts,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bank_account' => $this->route('bank_account'),
        ]);
    }
}
