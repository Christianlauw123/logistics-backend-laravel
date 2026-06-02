<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by middleware on the route
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle');
        return [
            'name'          => ['sometimes', 'string', 'min:1'],
            'plate_number'  => ['sometimes', 'string', 'min:1', "unique:vehicles,plate_number,{$vehicleId}"],
            'type'          => ['sometimes', 'string', 'min:1'],
            'capacity'      => ['sometimes', 'numeric', 'min:0.1'],
            'is_active'     => ['nullable','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa string',
            'name.min' => 'Nama harus minimal 1 karakter',
            'plate_number.string' => 'Nomor plat harus berupa string',
            'plate_number.min' => 'Nomor plat harus minimal 1 karakter',
            'plate_number.unique' => 'Nomor plat sudah digunakan',
            'type.string' => 'Tipe harus berupa string',
            'type.min' => 'Tipe harus minimal 1 karakter',
            'capacity.numeric' => 'Kapasitas harus berupa angka',
            'capacity.min' => 'Kapasitas harus bernilai positif',
        ];
    }
}
