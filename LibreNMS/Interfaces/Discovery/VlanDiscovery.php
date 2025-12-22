<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface VlanDiscovery
{
    /**
     * @return Collection<\App\Models\Vlan>
     */
    public function discoverVlans(): Collection;
}
