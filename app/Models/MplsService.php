<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsService extends DeviceRelatedModel implements Keyable
{
    protected $primaryKey = 'svc_id';
    public $timestamps = false;
    protected $fillable = [
        'svc_oid',
        'device_id',
        'svcRowStatus',
        'svcType',
        'svcCustId',
        'svcAdminStatus',
        'svcOperStatus',
        'svcDescription',
        'svcMtu',
        'svcNumSaps',
        'svcNumSdps',
        'svcLastMgmtChange',
        'svcLastStatusChange',
        'svcVRouterId',
        'svcTlsMacLearning',
        'svcTlsStpAdminStatus',
        'svcTlsStpOperStatus',
        'svcTlsFdbTableSize',
        'svcTlsFdbNumEntries',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return (string) $this->svc_oid;
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
     * @return HasMany<MplsSap, $this>
     */
    public function saps(): HasMany
    {
        return $this->hasMany(MplsSap::class, 'svc_id');
    }
}
