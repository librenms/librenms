<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillPort extends PortRelatedModel
{
    protected $table = 'bill_ports';
    public $timestamps = false;

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }
}
