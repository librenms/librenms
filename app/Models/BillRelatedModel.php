<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class BillRelatedModel extends BaseModel
{
    // ---- Query scopes ----

    public function scopeHasAccess(Builder $query, User $user): Builder
    {
        return $this->hasBillAccess($query, $user);
    }

    // ---- Define Relationships ----

    /**
     * @return BelongsTo<Bill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }
}
