<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VmPort extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = 'last_seen';

    // --- Relationships ---

    public function vm(): BelongsTo
    {
        return $this->belongsTo(Vminfo::class, 'vm_id');
    }
}
