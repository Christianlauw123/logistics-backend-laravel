<?php

namespace App\Http\Requests\TripPrice;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'                => ['required', 'uuid', 'exists:customers,id'],
            'origin_sub_district_id'      => ['required', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id' => [
                'required',
                'uuid',
                'exists:sub_districts,id',
                'different:origin_sub_district_id', // origin and destination cannot be the same
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'weight_category' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'base_price.required' => 'Harga dasar harus diisi',
            'base_price.numeric' => 'Harga dasar harus berupa angka',
            'base_price.min' => 'Harga dasar harus bernilai positif',
            'weight_category.required' => 'Kategori harus diisi',
            'weight_category.numeric' => 'Kategori harus berupa angka',
            'weight_category.min' => 'Kategori harus bernilai positif',
            'customer_id.required' => 'Pelanggan harus diisi',
            'customer_id.uuid' => 'Pelanggan tidak valid',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'origin_sub_district_id.required' => 'Kecamatan asal harus diisi',
            'origin_sub_district_id.uuid' => 'Kecamatan asal tidak valid',
            'origin_sub_district_id.exists' => 'Kecamatan asal tidak ditemukan',
            'dest_sub_district_id.required' => 'Kecamatan tujuan harus diisi',
            'dest_sub_district_id.uuid' => 'Kecamatan tujuan tidak valid',
            'dest_sub_district_id.exists' => 'Kecamatan tujuan tidak ditemukan',
            'dest_sub_district_id.different' => 'Kecamatan tujuan harus berbeda dengan kecamatan asal',
        ];
    }
}
