<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class BillHistory extends BillRelatedModel
{
    protected $table = 'bill_history';
    const CREATED_AT = null;
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'bill_datefrom',
        'bill_dateto',
        'bill_type',
        'bill_allowed',
        'bill_used',
        'bill_overuse',
        'bill_percent',
        'rate_95th_in',
        'rate_95th_out',
        'rate_95th',
        'dir_95th',
        'rate_average',
        'rate_average_in',
        'rate_average_out',
        'traf_in',
        'traf_out',
        'traf_total',
        'bill_peak_out',
        'bill_peak_in',
        'pdf',
    ];

    // ---- Query scopes ----

    public function scopeCurrent(Builder $query): Builder
    {
        $now = now();

        return $query->where('bill_datefrom', '<=', $now)
            ->where('bill_dateto', '>=', $now);
    }
}
