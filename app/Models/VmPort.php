<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\HasMetricTags;
use LibreNMS\Interfaces\Models\Keyable;

class VmPort extends Model implements HasMetricTags, Keyable
{
    const CREATED_AT = null;
    const UPDATED_AT = 'last_seen';

    // --- Relationships ---

    /**
     * @return BelongsTo<Vminfo, $this>
     */
    public function vm(): BelongsTo
    {
        return $this->belongsTo(Vminfo::class, 'vm_id');
    }

    public function getCompositeKey(): string
    {
        return "$this->vm_id-$this->port";
    }

    public function tags(): array
    {
        return [
            'vm_id' => $this->vm_id,
            'port' => $this->port,
        ];
    }
}
