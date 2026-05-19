<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class ShowAttachmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'attachment'  => ['required', 'uuid', 'exists:attachments,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'attachment' => $this->route('attachment'),
        ]);
    }
}
