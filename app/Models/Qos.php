<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class Qos extends Model implements Keyable
{
    use HasFactory;
    protected $primaryKey = 'qos_id';
    protected $fillable = [
        'device_id',
        'port_id',
        'parent_id',
        'type',
        'title',
        'tooltip',
        'snmp_idx',
        'rrd_id',
        'ingress',
        'egress',
    ];

    // Array to store additional data during polling that is not part of the model
    public $poll_data = [];

    /**
     * Get a string that can identify a unique instance of this model
     *
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->device_id . '-' . $this->type . '-' . $this->rrd_id;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Qos::class, 'parent_id', 'qos_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Qos::class, 'qos_id', 'parent_id');
    }
}
