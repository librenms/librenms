<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsLsp extends DeviceRelatedModel implements Keyable
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
     */
    public function getCompositeKey(): string
    {
        return $this->vrf_oid . '-' . $this->lsp_oid;
    }

    // ---- Define Relationships ----
    /**
     * @return HasMany<MplsLspPath, $this>
     */
    public function paths(): HasMany
    {
        return $this->hasMany(MplsLspPath::class, 'lsp_id');
    }

    public function vrf(): BelongsTo
    {
        return $this->belongsTo(Vrf::class, 'vrf_oid', 'vrf_oid')
            ->whereColumn('mpls_lsps.device_id', 'vrfs.device_id');
    }
}
