<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface PortVlanDiscovery
{
    /**
     * @param  Collection<\App\Models\Vlan>  $vlans
     * @return Collection<\App\Models\PortVlan>
     */
    public function discoverPortVlanData(Collection $vlans): Collection;
}
