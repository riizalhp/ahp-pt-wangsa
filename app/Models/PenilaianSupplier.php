<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianSupplier extends Model
{
    protected $table = 'penilaian_supplier';

    const UPDATED_AT = null;

    protected $fillable = [
        'subkriteria_id',
        'a_supplier_id',
        'b_supplier_id',
        'nilai',
    ];

    public function subkriteria(): BelongsTo
    {
        return $this->belongsTo(Subkriteria::class, 'subkriteria_id');
    }

    public function supplierA(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'a_supplier_id');
    }

    public function supplierB(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'b_supplier_id');
    }
}
