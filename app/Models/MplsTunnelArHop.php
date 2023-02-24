<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class MplsTunnelArHop extends Model implements Keyable
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
     *
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->mplsTunnelARHopListIndex . '-' . $this->mplsTunnelARHopIndex;
    }
}
