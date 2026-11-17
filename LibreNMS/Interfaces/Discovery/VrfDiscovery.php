<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;

interface VrfDiscovery
{
    /**
     * Discover the VRFs (routing instances) of the device.
     * vrf_oid is the VRF index used in the vendor MIB, or the VRF name when the MIB has no index.
     * Set context_name when the device exposes the VRF in its own SNMP context.
     * Call throwIfConnectionError() on the queries that list the VRFs, so a device that did not
     * answer is not mistaken for a device without VRFs. Optional probes may skip it.
     *
     * @return Collection<int, Vrf>
     *
     * @throws SnmpException when the device did not answer, existing VRFs are kept
     */
    public function discoverVrfs(): Collection;
}
