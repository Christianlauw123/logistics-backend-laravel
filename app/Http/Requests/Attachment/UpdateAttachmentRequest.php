<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        return [
            'file'                      => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'transaction_id'            => ['nullable', 'uuid', 'required_without:transaction_detail_id', 'prohibited_with:transaction_detail_id', 'exists:transactions,id'],
            'transaction_detail_id'     => ['nullable', 'uuid', 'required_without:transaction_id', 'prohibited_with:transaction_id', 'exists:transaction_details,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File harus ada',
            'file.file' => 'File harus berupa file yang valid',
            'file.mimes' => 'File harus berupa gambar (jpg, jpeg, png) atau PDF',
            'file.max' => 'Ukuran file maksimal < 2MB',
        ];
    }
}
