<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'customer_id'               => ['required', 'uuid', 'exists:customers,id'],
            'vehicle_id'                => ['required', 'uuid', 'exists:vehicles,id'],
            'bank_account_id'           => ['required', 'uuid', 'exists:bank_accounts,id'],
            'dest_address'              => ['required', 'string', 'max:255'],
            'do_number'                 => ['nullable', 'string', 'unique:transactions,do_number'],
            'do_actual_date'            => ['nullable', 'date'],
            'transaction_capacity'      => ['required', 'numeric', 'min:0'],
            'transaction_items'         => ['required', 'string', 'max:255'],
            'origin_sub_district_id'    => ['required', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id'      => ['required', 'uuid', 'exists:sub_districts,id'],
            'note'                      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'do_number.unique' => 'This DO number already exists.',
        ];
    }
}
