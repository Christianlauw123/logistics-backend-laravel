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
            'vehicle_id'            => ['sometimes', 'uuid', 'exists:vehicles,id'],
            'bank_account_id'       => ['sometimes', 'uuid', 'exists:bank_accounts,id'],
            'dest_address'          => ['sometimes', 'string', 'max:255'],
            'do_number'             => ['sometimes', 'string', "unique:transactions,do_number,{$transactionId}"],
            'do_actual_date'        => ['sometimes', 'date'],
            'transaction_capacity'  => ['sometimes', 'numeric', 'min:0'],
            'transaction_items'     => ['sometimes', 'string', 'max:255'],
            'origin_sub_district_id'=> ['sometimes', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id'  => ['sometimes', 'uuid', 'exists:sub_districts,id'],
            'note'                  => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'do_number.unique' => 'No DO sudah terpakai',
            'do_actual_date.date' => 'Tanggal DO aktual harus berupa tanggal yang valid',
            'transaction_capacity.min' => 'Kapasitas transaksi harus bernilai positif',
            'transaction_items.max' => 'Item transaksi maksimal 255 karakter',
            'customer_id.uuid' => 'Pelanggan tidak valid',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'vehicle_id.uuid' => 'Kendaraan tidak valid',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan',
            'bank_account_id.uuid' => 'Akun bank tidak valid',
            'bank_account_id.exists' => 'Akun bank tidak ditemukan',
            'origin_sub_district_id.uuid' => 'Kecamatan asal tidak valid',
            'origin_sub_district_id.exists' => 'Kecamatan asal tidak ditemukan',
            'dest_sub_district_id.uuid' => 'Kecamatan tujuan tidak valid',
            'dest_sub_district_id.exists' => 'Kecamatan tujuan tidak ditemukan',
        ];
    }
}
