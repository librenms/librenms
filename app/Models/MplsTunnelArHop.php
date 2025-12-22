<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class MplsTunnelArHop extends DeviceRelatedModel implements Keyable
{
    protected $primaryKey = 'ar_hop_id';
    public $timestamps = false;
    protected $fillable = [
        'ar_hop_id',
        'mplsTunnelARHopListIndex',
        'mplsTunnelARHopIndex',
        'lsp_path_id',
        'device_id',
        'mplsTunnelARHopAddrType',
        'mplsTunnelARHopIpv4Addr',
        'mplsTunnelARHopIpv6Addr',
        'mplsTunnelARHopAsNumber',
        'mplsTunnelARHopStrictOrLoose',
        'mplsTunnelARHopRouterId',
        'localProtected',
        'linkProtectionInUse',
        'bandwidthProtected',
        'nextNodeProtected',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return $this->mplsTunnelARHopListIndex . '-' . $this->mplsTunnelARHopIndex;
    }

    // ---- Define Relationships ----

    /**
     * @return BelongsTo<MplsLspPath, $this>
     */
    public function lspPath(): BelongsTo
    {
        return $this->belongsTo(MplsLspPath::class, 'lsp_path_id');
    }
}
