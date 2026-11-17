<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;

interface VrfPortDiscovery
{
    /**
     * Find the interfaces in each VRF. Return the member ports with ifVrf set to the vrf_id of their VRF.
     *
     * @param  Collection<int, Vrf>  $vrfs  saved VRFs
     * @return Collection<int, Port>
     *
     * @throws SnmpException when the device did not answer, existing members are kept
     */
    public function discoverVrfPorts(Collection $vrfs): Collection;
}
