<?php

namespace App\Models;

class BillData extends BillRelatedModel
{
    protected $table = 'bill_data';
    const CREATED_AT = 'timestamp';
    const UPDATED_AT = null;

    protected $fillable = [
        'bill_id',
        'timestamp',
        'period',
        'delta',
        'in_delta',
        'out_delta',
    ];
}
