<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        $bankAccountId = $this->route('bank_account');
        return [
            'bank_name'                 => ['nullable', 'string', 'min:1'],
            'account_identifier_number' => ['required', 'string', "unique:bank_accounts,account_identifier_number,{$bankAccountId}"],
            'account_name'              => ['nullable', 'string', 'min:1'],
            'account_number'            => ['nullable', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_identifier_number.unique' => 'Nomor unik akun ini sudah ada',
            'account_identifier_number.required' => 'Nomor unik akun harus diisi',
        ];
    }
}
