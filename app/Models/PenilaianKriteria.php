<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianKriteria extends Model
{
    protected $table = 'penilaian_kriteria';

    const UPDATED_AT = null;

    protected $fillable = [
        'a_id',
        'b_id',
        'nilai',
    ];

    public function kriteriaA(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'a_id');
    }

    public function kriteriaB(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'b_id');
    }
}
