<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ShowUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user'  => ['required', 'uuid', 'exists:users,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user' => $this->route('user'),
        ]);
    }
}
