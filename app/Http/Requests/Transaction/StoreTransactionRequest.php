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
            'trip_price_id'             => ['required', 'uuid', 'exists:trip_prices,id'],
            'vehicle_id'                => ['required', 'uuid', 'exists:vehicles,id'],
            'bank_account_id'           => ['required', 'uuid', 'exists:bank_accounts,id'],
            'dest_address'              => ['required', 'string', 'max:255'],
            'do_number'                 => ['required', 'string', 'unique:transactions,do_number'],
            'do_date'                   => ['required', 'date'],
            'do_actual_date'            => ['nullable', 'date'],
            'transaction_capacity'      => ['nullable', 'numeric', 'min:0'],
            'transaction_items'         => ['nullable', 'string', 'max:255'],
            'origin_sub_district_id'    => ['required', 'string', 'exists:sub_districts,id'],
            'dest_sub_district_id'      => ['required', 'string', 'exists:sub_districts,id']
        ];
    }

    public function messages(): array
    {
        return [
            'do_number.unique' => 'This DO number already exists.',
        ];
    }
}
