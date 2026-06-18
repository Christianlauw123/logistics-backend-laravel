<?php

namespace App\Http\Requests\Attachment;

use App\Enums\Attachments\AttachmentStatus;
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
        if (request()->user()->role->name !== 'Super Admin')
            return [
                'status' => ['required',  Rule::in(array_column(AttachmentStatus::cases(),'value'))],
            ];

        return [

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
