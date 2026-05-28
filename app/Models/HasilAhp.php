<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilAhp extends Model
{
    protected $table = 'data_hasil_ahp';

    const CREATED_AT = 'computed_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'supplier_id',
        'nilai_akhir',
        'ranking',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
