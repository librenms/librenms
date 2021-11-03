<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class MplsTunnelCHop extends Model implements Keyable
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
     *
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->mplsTunnelCHopListIndex . '-' . $this->mplsTunnelCHopIndex;
    }
}
