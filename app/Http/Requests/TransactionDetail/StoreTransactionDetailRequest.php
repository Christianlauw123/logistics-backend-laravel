<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'numeric', 'min:0.1'],
            'note'              => ['nullable', 'string'],
            'purpose'           => ['nullable', 'string'],
            'transaction_id'    => ['required', 'uuid', 'exists:transactions,id'],
            'is_special_case'   => ['nullable', 'boolean'],
            'file'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048', 'required_if:is_special_case,true,true'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah harus bernilai positif',
            'file.file' => 'File harus berupa file yang valid',
            'file.mimes' => 'File harus berupa gambar (jpg, jpeg, png)',
            'file.max' => 'Ukuran file maksimal < 2MB',
            'file.required_if' => 'File bukti diperlukan'
        ];
    }
}
