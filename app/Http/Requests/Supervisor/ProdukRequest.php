<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class ProdukRequest extends FormRequest
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
     * Validates: Requirements 2.1, 2.3, 2.4, 2.5
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id'       => 'required|exists:data_supplier,id',
            'nama'              => 'required|string|max:255',
            'jenis_produk'      => 'nullable|string|max:150',
            'merk'              => 'nullable|string|max:100',
            'ukuran'            => 'nullable|string|max:100',
            'kapasitas_pasokan' => 'nullable|string|max:150',
            'kode'              => 'nullable|string|max:50',
            'satuan'            => 'nullable|string|max:50',
            'harga'             => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists'   => 'Supplier yang dipilih tidak valid.',
            'nama.required'        => 'Nama produk wajib diisi.',
        ];
    }
}
