<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'revision_dest_sub_district_id'  => ['required', 'uuid', 'exists:sub_districts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'revision_dest_sub_district_id.uuid' => 'Kecamatan revisi tujuan tidak valid',
            'revision_dest_sub_district_id.exists' => 'Kecamatan revisi tujuan tidak ditemukan',
        ];
    }
}
