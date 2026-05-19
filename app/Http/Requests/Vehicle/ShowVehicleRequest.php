<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class ShowVehicleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle'  => ['required', 'uuid', 'exists:vehicles,id']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle' => $this->route('vehicle'),
        ]);
    }
}
