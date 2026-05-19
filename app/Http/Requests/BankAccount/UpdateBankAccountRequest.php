<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bank_account' => $this->route('bank_account'),
        ]);
    }

    public function rules(): array
    {
        $bankAccountId = $this->route('bank_account');

        return [
            'bank_account'              => ['required', 'uuid', 'exists:bank_accounts,id'],

            'bank_name'                 => ['sometimes', 'string', 'min:1'],
            'account_identifier_number' => ['required', 'string', "unique:bank_accounts,account_identifier_number,{$bankAccountId}"],
            'account_name'              => ['sometimes', 'string', 'min:1'],
            'account_number'            => ['sometimes', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_identifier_number.unique' => 'This account_identifier_number number already exists.',
        ];
    }
}
