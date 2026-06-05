<?php

namespace App\Models;

use App\Models\PengadaanHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'data_supplier';

    protected $fillable = [
        'kode',
        'nama',
        'jenis_barang',
        'alamat',
        'kontak_person',
        'telepon',
        'lama_kerja_sama',
        'email',
        'mean_hari_keterlambatan',
        'total_persen_cacat',
        'total_persen_keterlambatan',
    ];

    public function header(): HasMany
    {
        return $this->hasMany(PengadaanHeader::class, 'supplier_id');
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'supplier_id');
    }

    public function pengadaan(): HasMany
    {
        return $this->hasMany(Pengadaan::class, 'supplier_id');
    }

    public function penilaianA(): HasMany
    {
        return $this->hasMany(PenilaianSupplier::class, 'a_supplier_id');
    }

    public function penilaianB(): HasMany
    {
        return $this->hasMany(PenilaianSupplier::class, 'b_supplier_id');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(HasilAhp::class, 'supplier_id');
    }
}
