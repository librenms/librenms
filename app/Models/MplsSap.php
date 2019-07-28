<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class MplsSap extends Model implements Keyable
{
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
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->svc_oid . '-' . $this->sapPortId . '-' . $this->sapEncapValue;
    }

    // ---- Define Relationships ----

    public function binds()
    {
        return $this->hasMany('App\Models\MplsSdpBind', 'svc_id');
    }
    
    public function services()
    {
        return $this->hasMany('App\Models\MplsService', 'svc_id');
    }
}
