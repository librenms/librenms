<?php

namespace App\Models;

class BillSapCounter extends BillRelatedModel
{
    protected $table = 'bill_sap_counters';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'sap_id',
        'bill_id',
        'timestamp',
        'in_counter',
        'in_delta',
        'out_counter',
        'out_delta',
    ];
}
