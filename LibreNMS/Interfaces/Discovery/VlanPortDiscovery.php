<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface VlanPortDiscovery
{
    /**
     * @param  Collection<\App\Models\Vlan>  $vlans
     * @return Collection<\App\Models\PortVlan>
     */
    public function discoverVlanPorts(Collection $vlans): Collection;
}
