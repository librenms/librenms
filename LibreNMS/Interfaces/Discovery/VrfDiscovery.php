<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Vrf;
use Illuminate\Support\Collection;

interface VrfDiscovery
{
    /**
     * Discover the VRFs (routing instances) of the device.
     * vrf_oid is the VRF index used in the vendor MIB, or the VRF name when the MIB has no index.
     * Set context_name when the device exposes the VRF in its own SNMP context.
     *
     * @return Collection<int, Vrf>
     */
    public function discoverVrfs(): Collection;
}
