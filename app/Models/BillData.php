<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

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

    // ---- Query scopes ----

    /**
     * Rows belonging to a billing period: after the start, up to and including the end.
     */
    protected function scopeInBillingPeriod(Builder $query, string $datefrom, string $dateto): Builder
    {
        return $query->where('timestamp', '>', $datefrom)
            ->where('timestamp', '<=', $dateto);
    }

    /**
     * Rows between two unix timestamps, inclusive.
     */
    protected function scopeBetweenUnixTimes(Builder $query, int|string|null $from, int|string|null $to): Builder
    {
        return $query->whereRaw('`timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?)', [$from, $to]);
    }
}
