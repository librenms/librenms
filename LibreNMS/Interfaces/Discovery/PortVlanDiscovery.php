<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface PortVlanDiscovery
{
    /**
     * @return Collection<\App\Models\Vlan>
     */
    public function discoverPortVlanData(): Collection;
}
