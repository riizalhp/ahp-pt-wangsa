<?php

namespace App\Http\Requests\Logistik;

use Illuminate\Foundation\Http\FormRequest;

class PenerimaanRequest extends FormRequest
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
     * Validates: Requirements 4.3, 4.4, 4.5
     *
     * NOTE: The cross-field business rules below are NOT validated here because
     * they require database values that are not available inside a FormRequest:
     *   - Req 4.4: jumlah_diterima_baik <= jumlah_dipesan (requires the detail row's
     *              jumlah_dipesan value from data_pengadaan_detail)
     *   - Req 4.5: tanggal_kedatangan_aktual >= tanggal_po (requires the header row's
     *              tanggal_po value from data_pengadaan_header)
     * Both checks are performed in PenerimaanController::update() after the header
     * and detail records have been loaded from the database.
     *
     * The form submits receiving data as a keyed array per detail line item:
     *   items[{detail_id}][jumlah_diterima_baik]
     *   items[{detail_id}][tanggal_kedatangan_aktual]
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'items'                               => 'required|array|min:1',
            'items.*.jumlah_diterima_baik'        => 'required|numeric|min:0|max:99999.99',
            'items.*.tanggal_kedatangan_aktual'   => 'required|date',
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
            'items.*.jumlah_diterima_baik.required'      => 'Jumlah diterima baik wajib diisi.',
            'items.*.jumlah_diterima_baik.min'           => 'Jumlah diterima tidak boleh negatif.',
            'items.*.tanggal_kedatangan_aktual.required' => 'Tanggal kedatangan aktual wajib diisi.',
            'items.*.tanggal_kedatangan_aktual.date'     => 'Format tanggal tidak valid.',
        ];
    }
}
