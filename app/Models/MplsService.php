<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsService extends Model implements Keyable
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
     *
     * @return int
     */
    public function getCompositeKey()
    {
        return $this->svc_oid;
    }

    // ---- Define Relationships ----

    public function binds(): HasMany
    {
        return $this->hasMany(\App\Models\MplsSdpBind::class, 'svc_id');
    }
}
