<?php

namespace App\Http\Requests\TripPrice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'                => ['sometimes', 'uuid', 'exists:customers,id'],
            'origin_sub_district_id'      => ['sometimes', 'uuid', 'exists:sub_districts,id'],
            'dest_sub_district_id' => [
                'sometimes',
                'uuid',
                'exists:sub_districts,id',
                'different:origin_sub_district_id', // origin and destination cannot be the same
            ],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'weight_category' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'base_price.numeric' => 'Harga dasar harus berupa angka',
            'base_price.min' => 'Harga dasar harus bernilai positif',
            'weight_category.numeric' => 'Kategori harus berupa angka',
            'weight_category.min' => 'Kategori harus bernilai positif',
            'customer_id.uuid' => 'Pelanggan tidak valid',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'origin_sub_district_id.uuid' => 'Kecamatan asal tidak valid',
            'origin_sub_district_id.exists' => 'Kecamatan asal tidak ditemukan',
            'dest_sub_district_id.uuid' => 'Kecamatan tujuan tidak valid',
            'dest_sub_district_id.exists' => 'Kecamatan tujuan tidak ditemukan',
            'dest_sub_district_id.different' => 'Kecamatan tujuan harus berbeda dengan kecamatan asal',
        ];
    }
}
