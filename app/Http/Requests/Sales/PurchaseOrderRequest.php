<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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
     * Validates: Requirements 3.2, 3.3, 3.4, 3.5, 3.6, 3.7
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supplier_id'                  => 'required|exists:data_supplier,id',
            'no_po'                        => 'required|string|max:100|unique:data_pengadaan_header,no_po',
            'tanggal_po'                   => 'required|date',
            'tanggal_kedatangan_target'    => 'required|date|after_or_equal:tanggal_po',
            'catatan'                      => 'nullable|string',
            'foto'                         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'items'                        => 'required|array|min:1',
            'items.*.produk_id'            => 'required|exists:data_produk,id',
            'items.*.jumlah_dipesan'       => 'required|numeric|gt:0|max:99999.99',
            'items.*.satuan'               => 'required|string|max:50',
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
            'no_po.unique'                         => 'Nomor PO sudah digunakan.',
            'tanggal_kedatangan_target.after_or_equal' => 'Tanggal kedatangan tidak boleh sebelum tanggal PO.',
            'foto.image'                           => 'File harus berupa gambar.',
            'foto.mimes'                           => 'Format foto harus: JPG, JPEG, atau PNG.',
            'foto.max'                             => 'Ukuran foto maksimal 2MB.',
            'items.required'                       => 'Minimal satu produk harus ditambahkan.',
            'items.min'                            => 'Minimal satu produk harus ditambahkan.',
            'items.*.produk_id.required'           => 'Produk wajib dipilih.',
            'items.*.jumlah_dipesan.required'      => 'Jumlah dipesan wajib diisi.',
            'items.*.jumlah_dipesan.gt'            => 'Jumlah dipesan harus lebih dari 0.',
            'items.*.satuan.required'              => 'Satuan wajib diisi.',
        ];
    }
}
