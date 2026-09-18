<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Links a billable source (port, SAP, ...) to a bill and holds the last
 * counter sample billing took from it.
 */
class BillCounter extends MorphPivot
{
    protected $table = 'bill_counters';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bill_id',
        'source_type',
        'source_id',
        'autoadded',
        'timestamp',
        'in_counter',
        'in_delta',
        'out_counter',
        'out_delta',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'in_counter' => 'integer',
        'in_delta' => 'integer',
        'out_counter' => 'integer',
        'out_delta' => 'integer',
    ];

    /**
     * @return BelongsTo<Bill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }

    /**
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo('source');
    }
}
