<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengadaanHeader extends Model
{
    protected $table = 'data_pengadaan_header';

    protected $fillable = [
        'supplier_id',
        'no_po',
        'tanggal_po',
        'tanggal_kedatangan_target',
        'catatan',
        'foto',
    ];

    protected $casts = [
        'tanggal_po' => 'date',
        'tanggal_kedatangan_target' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PengadaanDetail::class, 'pengadaan_id');
    }
}
