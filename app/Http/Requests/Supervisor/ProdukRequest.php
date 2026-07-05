<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $produkId = $this->route('produk');

        // Build ukuran value for validation (same logic as in controller)
        $ukuran = $this->input('ukuran');
        if (empty($ukuran) && (!empty($this->input('lebar')) || !empty($this->input('panjang')) || !empty($this->input('tinggi')))) {
            $dimensions = [];
            if (!empty($this->input('lebar'))) $dimensions[] = $this->input('lebar');
            if (!empty($this->input('panjang'))) $dimensions[] = $this->input('panjang');
            if (!empty($this->input('tinggi'))) $dimensions[] = $this->input('tinggi');
            $ukuran = implode(' × ', $dimensions);
        }

        return [
            'supplier_id'       => 'required|exists:data_supplier,id',
            'nama'              => 'required|string|max:255',
            'jenis_produk'      => 'nullable|string|max:150',
            'merk'              => [
                'required',
                'string',
                'max:100',
                Rule::unique('data_produk')->where(function ($query) use ($ukuran) {
                    $q = $query
                        ->where('supplier_id', $this->input('supplier_id'))
                        ->where('nama', $this->input('nama'));
                    
                    // Add ukuran to uniqueness check
                    if ($ukuran) {
                        $q->where('ukuran', $ukuran);
                    } else {
                        $q->whereNull('ukuran');
                    }
                    
                    return $q;
                })->ignore($produkId),
            ],
            'ukuran'            => 'nullable|string|max:100',
            'panjang'           => 'nullable|string|max:50',
            'lebar'             => 'nullable|string|max:50',
            'tinggi'            => 'nullable|string|max:50',
            'kapasitas_pasokan' => 'nullable|string|max:150',
            'kapasitas_nilai'   => 'nullable|string|max:100',
            'kapasitas_satuan'  => 'nullable|string|max:50',
            'kode'              => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('data_produk')->ignore($produkId),
            ],
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
            'merk.unique'          => 'Produk dengan kombinasi supplier, nama, merk, dan ukuran yang sama sudah terdaftar. Jika ukuran berbeda, silakan input ulang.',
        ];
    }
}
