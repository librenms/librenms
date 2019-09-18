<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class MplsSdp extends Model implements Keyable
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
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->sdp_oid;
    }

    // ---- Define Relationships ----

    public function binds()
    {
        return $this->hasMany('App\Models\MplsSdpBind', 'sdp_id');
    }
}
