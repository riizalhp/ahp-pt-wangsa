<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'data_supplier';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'telepon',
        'email',
        'mean_hari_keterlambatan',
        'total_persen_cacat',
        'total_persen_keterlambatan',
    ];

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
