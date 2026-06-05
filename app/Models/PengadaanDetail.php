<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengadaanDetail extends Model
{
    protected $table = 'data_pengadaan_detail';

    protected $fillable = [
        'pengadaan_id',
        'produk_id',
        'jumlah_dipesan',
        'satuan',
        'jumlah_diterima_baik',
        'tanggal_kedatangan_aktual',
        'persen_kualitas_item',
        'hari_keterlambatan',
    ];

    protected $casts = [
        'tanggal_kedatangan_aktual' => 'date',
        'jumlah_dipesan' => 'decimal:2',
        'jumlah_diterima_baik' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(PengadaanHeader::class, 'pengadaan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function isReceived(): bool
    {
        return ! is_null($this->jumlah_diterima_baik) && ! is_null($this->tanggal_kedatangan_aktual);
    }
}
