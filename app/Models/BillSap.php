<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillSap extends BillRelatedModel
{
    protected $table = 'bill_saps';
    public $timestamps = false;

    protected $fillable = [
        'bill_id',
        'sap_id',
        'bill_sap_autoadded',
    ];

    // ---- Define Relationships ----

    /**
     * @return BelongsTo<MplsSap, $this>
     */
    public function sap(): BelongsTo
    {
        return $this->belongsTo(MplsSap::class, 'sap_id', 'sap_id');
    }
}
