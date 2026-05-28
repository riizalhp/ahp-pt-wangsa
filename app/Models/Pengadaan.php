<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengadaan extends Model
{
    protected $table = 'data_pengadaan';

    protected $fillable = [
        'supplier_id',
        'produk_id',
        'jumlah_dibeli',
        'tanggal_po',
        'tanggal_kedatangan',
        'jumlah_diterima',
        'jumlah_cacat',
        'persen_kualitas',
        'hari_keterlambatan',
        'foto_path',
        'catatan',
    ];

    protected $casts = [
        'tanggal_po' => 'date',
        'tanggal_kedatangan' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
