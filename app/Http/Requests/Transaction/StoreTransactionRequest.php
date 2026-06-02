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
            'dest_address'              => ['nullable', 'string', 'max:255'],
            'do_number'                 => ['nullable', 'string', 'unique:transactions,do_number'],
            'do_actual_date'            => ['nullable', 'date'],
            'transaction_capacity'      => ['required', 'numeric', 'min:0'],
            'transaction_items'         => ['nullable', 'string', 'max:255'],
            'origin_sub_district_id'    => ['required', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id'      => ['required', 'uuid', 'exists:sub_districts,id'],
            'note'                      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'do_number.unique' => 'No DO sudah terpakai',
            'do_actual_date.date' => 'Tanggal DO aktual harus berupa tanggal yang valid',
            'transaction_capacity.min' => 'Kapasitas transaksi harus bernilai positif',
            'transaction_items.max' => 'Item transaksi maksimal 255 karakter',
            'customer_id.required' => 'Pelanggan harus diisi',
            'customer_id.uuid' => 'Pelanggan tidak valid',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'vehicle_id.required' => 'Kendaraan harus diisi',
            'vehicle_id.uuid' => 'Kendaraan tidak valid',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan',
            'bank_account_id.required' => 'Akun bank harus diisi',
            'bank_account_id.uuid' => 'Akun bank tidak valid',
            'bank_account_id.exists' => 'Akun bank tidak ditemukan',
            'origin_sub_district_id.required' => 'Kecamatan asal harus diisi',
            'origin_sub_district_id.uuid' => 'Kecamatan asal tidak valid',
            'origin_sub_district_id.exists' => 'Kecamatan asal tidak ditemukan',
            'dest_sub_district_id.required' => 'Kecamatan tujuan harus diisi',
            'dest_sub_district_id.uuid' => 'Kecamatan tujuan tidak valid',
            'dest_sub_district_id.exists' => 'Kecamatan tujuan tidak ditemukan',
        ];
    }
}
