<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsSap extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    protected $primaryKey = 'sap_id';
    public $timestamps = false;
    protected $fillable = [
        'svc_id',
        'svc_oid',
        'sapPortId',
        'ifName',
        'sapEncapValue',
        'device_id',
        'sapRowStatus',
        'sapType',
        'sapDescription',
        'sapAdminStatus',
        'sapOperStatus',
        'sapLastMgmtChange',
        'sapLastStatusChange',
        'sapIngressOctets',
        'sapEgressOctets',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return $this->svc_oid . '-' . $this->sapPortId . '-' . $this->sapEncapValue;
    }

    // ---- Define Relationships ----
    /**
     * @return HasMany<MplsSdpBind, $this>
     */
    public function binds(): HasMany
    {
        return $this->hasMany(MplsSdpBind::class, 'svc_id');
    }

    /**
     * @return BelongsTo<MplsService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(MplsService::class, 'svc_id');
    }

    /**
     * @return BelongsToMany<Bill, $this>
     */
    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(Bill::class, 'bill_saps', 'sap_id', 'bill_id');
    }
}
