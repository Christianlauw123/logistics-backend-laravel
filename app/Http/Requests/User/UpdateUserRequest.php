<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'role_id'   => ['sometimes', 'uuid', 'exists:roles,id'],
            'name'      => ['sometimes', 'string', 'max:100'],
            'email'     => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'password'  => ['sometimes', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
