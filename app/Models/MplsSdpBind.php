<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function sdp()
    {
        return $this->belongsTo('App\Models\MplsSdp', 'sdp_id');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\MplsService', 'svc_id');
    }
}
