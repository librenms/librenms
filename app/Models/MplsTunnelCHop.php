<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class MplsTunnelCHop extends DeviceRelatedModel implements Keyable
{
    protected $primaryKey = 'c_hop_id';
    public $timestamps = false;
    protected $fillable = [
        'c_hop_id',
        'mplsTunnelCHopListIndex',
        'mplsTunnelCHopIndex',
        'lsp_path_id',
        'device_id',
        'mplsTunnelCHopAddrType',
        'mplsTunnelCHopIpv4Addr',
        'mplsTunnelCHopIpv6Addr',
        'mplsTunnelCHopAsNumber',
        'mplsTunnelCHopStrictOrLoose',
        'mplsTunnelCHopRouterId',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return $this->mplsTunnelCHopListIndex . '-' . $this->mplsTunnelCHopIndex;
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
