<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kriteria extends Model
{
    protected $table = 'data_kriteria';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    public function subkriteria(): HasMany
    {
        return $this->hasMany(Subkriteria::class, 'kriteria_id');
    }

    public function pkriteriaA(): HasMany
    {
        return $this->hasMany(PenilaianKriteria::class, 'a_id');
    }

    public function pkriteriaB(): HasMany
    {
        return $this->hasMany(PenilaianKriteria::class, 'b_id');
    }
}
