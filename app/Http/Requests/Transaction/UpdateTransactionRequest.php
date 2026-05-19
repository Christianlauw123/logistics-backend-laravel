<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $transactionId = $this->route('transaction'); // ignore self on unique check

        return [
            'customer_id'           => ['sometimes', 'uuid', 'exists:customers,id'],
            'trip_price_id'         => ['sometimes', 'uuid', 'exists:trip_prices,id'],
            'vehicle_id'            => ['sometimes', 'uuid', 'exists:vehicles,id'],
            'bank_account_id'       => ['sometimes', 'uuid', 'exists:bank_accounts,id'],
            'dest_address'          => ['sometimes', 'string', 'max:255'],
            'do_number'             => ['sometimes', 'string', "unique:transactions,do_number,{$transactionId}"],
            'do_date'               => ['sometimes', 'date'],
            'do_actual_date'        => ['nullable', 'date'],
            'transaction_capacity'  => ['nullable', 'numeric', 'min:0'],
            'transaction_items'     => ['nullable', 'string', 'max:255'],
            'origin_sub_district_id'=> ['sometimes', 'string', 'exists:sub_districts,id'],
            'dest_sub_district_id'  => ['sometimes', 'string', 'exists:sub_districts,id']
        ];
    }
}
