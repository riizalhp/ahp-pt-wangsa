<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'data_produk';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function pengadaan(): HasMany
    {
        return $this->hasMany(Pengadaan::class, 'produk_id');
    }
}
