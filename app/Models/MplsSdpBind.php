<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class MplsSdpBind extends Model implements Keyable
{
    protected $primaryKey = 'bind_id';
    public $timestamps = false;
    protected $fillable = [
        'sdp_id',
        'svc_id',
        'sdp_oid',
        'svc_oid',
        'device_id',
        'sdpBindRowStatus',
        'sdpBindAdminStatus',
        'sdpBindOperStatus',
        'sdpBindLastMgmtChange',
        'sdpBindLastStatusChange',
        'sdpBindType',
        'sdpBindVcType',
        'sdpBindBaseStatsIngFwdPackets',
        'sdpBindBaseStatsIngFwdOctets',
        'sdpBindBaseStatsEgrFwdPackets',
        'sdpBindBaseStatsEgrFwdOctets',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->sdp_oid . '-' . $this->svc_oid;
    }

    // ---- Define Relationships ----

    public function sdp(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MplsSdp::class, 'sdp_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MplsService::class, 'svc_id');
    }
}
