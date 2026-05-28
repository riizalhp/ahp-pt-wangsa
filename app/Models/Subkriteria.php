<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subkriteria extends Model
{
    protected $table = 'data_subkriteria';

    protected $fillable = [
        'kriteria_id',
        'kode',
        'nama',
        'deskripsi',
    ];

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }

    public function pSubA(): HasMany
    {
        return $this->hasMany(PenilaianSubkriteria::class, 'a_id');
    }

    public function pSubB(): HasMany
    {
        return $this->hasMany(PenilaianSubkriteria::class, 'b_id');
    }

    public function pSupplier(): HasMany
    {
        return $this->hasMany(PenilaianSupplier::class, 'subkriteria_id');
    }
}
