<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttachmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['PENDING', 'VERIFIED', 'REJECTED'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status harus ada',
            'status.in' => 'Status tidak valid',
        ];
    }
}
