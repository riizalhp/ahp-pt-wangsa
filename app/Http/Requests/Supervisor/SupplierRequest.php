<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates: Requirements 1.1, 1.2, 1.3, 1.4
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $supplierId = $this->route('supplier');

        return [
            'kode' => [
                'required',
                'string',
                'max:50',
                $supplierId
                    ? Rule::unique('data_supplier', 'kode')->ignore($supplierId)
                    : 'unique:data_supplier,kode',
            ],
            'nama'           => 'required|string|max:255',
            'jenis_barang'   => 'nullable|string|max:100',
            'alamat'         => 'nullable|string',
            'kontak_person'  => 'nullable|string|max:150',
            'telepon'        => 'nullable|string|max:30',
            'lama_kerja_sama' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode.required' => 'Kode supplier wajib diisi.',
            'kode.unique'   => 'Kode supplier sudah digunakan.',
            'nama.required' => 'Nama supplier wajib diisi.',
        ];
    }
}
