<?php

namespace App\Models;

class BillPortCounter extends BillRelatedModel
{
    protected $table = 'bill_port_counters';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'port_id',
        'bill_id',
        'timestamp',
        'in_counter',
        'in_delta',
        'out_counter',
        'out_delta',
    ];
}
