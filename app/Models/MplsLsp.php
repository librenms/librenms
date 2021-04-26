<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsLsp extends Model implements Keyable
{
    protected $primaryKey = 'lsp_id';
    public $timestamps = false;
    protected $fillable = [
        'vrf_oid',
        'lsp_oid',
        'device_id',
        'mplsLspRowStatus',
        'mplsLspLastChange',
        'mplsLspName',
        'mplsLspAdminState',
        'mplsLspOperState',
        'mplsLspFromAddr',
        'mplsLspToAddr',
        'mplsLspType',
        'mplsLspFastReroute',
        'mplsLspAge',
        'mplsLspTimeUp',
        'mplsLspTimeDown',
        'mplsLspPrimaryTimeUp',
        'mplsLspTransitions',
        'mplsLspLastTransition',
        'mplsLspConfiguredPaths',
        'mplsLspStandbyPaths',
        'mplsLspOperationalPaths',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->vrf_oid . '-' . $this->lsp_oid;
    }

    // ---- Define Relationships ----

    public function paths(): HasMany
    {
        return $this->hasMany(\App\Models\MplsLspPath::class, 'lsp_id');
    }
}
