<?php

namespace App\Http\Requests\TransactionDetail;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_special_case' => filter_var($this->is_special_case, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'numeric', 'min:0.1'],
            'note'              => ['nullable', 'string'],
            'purpose'           => ['nullable', 'string'],
            'file'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048', 'required_if:is_special_case,true,true'],
            'is_special_case'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah harus bernilai positif',
            'file.file' => 'File harus berupa file yang valid',
            'file.mimes' => 'File harus berupa gambar (jpg, jpeg, png)',
            'file.max' => 'Ukuran file maksimal < 2MB',
            'file.required_if' => 'File bukti diperlukan'
        ];
    }
}
