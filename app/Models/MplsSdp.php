<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsSdp extends DeviceRelatedModel implements Keyable
{
    protected $primaryKey = 'sdp_id';
    public $timestamps = false;
    protected $fillable = [
        'sdp_oid',
        'device_id',
        'sdpRowStatus',
        'sdpDelivery',
        'sdpDescription',
        'sdpAdminStatus',
        'sdpOperStatus',
        'sdpAdminPathMtu',
        'sdpOperPathMtu',
        'sdpLastMgmtChange',
        'sdpLastStatusChange',
        'sdpActiveLspType',
        'sdpFarEndInetAddressType',
        'sdpFarEndInetAddress',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return (string) $this->sdp_oid;
    }

    // ---- Define Relationships ----
    /**
     * @return HasMany<MplsSdpBind, $this>
     */
    public function binds(): HasMany
    {
        return $this->hasMany(MplsSdpBind::class, 'sdp_id');
    }
}
