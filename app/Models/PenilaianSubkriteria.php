<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianSubkriteria extends Model
{
    protected $table = 'penilaian_subkriteria';

    const UPDATED_AT = null;

    protected $fillable = [
        'kriteria_id',
        'a_id',
        'b_id',
        'nilai',
    ];

    public function subkriteriaA(): BelongsTo
    {
        return $this->belongsTo(Subkriteria::class, 'a_id');
    }

    public function subkriteriaB(): BelongsTo
    {
        return $this->belongsTo(Subkriteria::class, 'b_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }
}
