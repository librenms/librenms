<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface BasicVlanDiscovery
{
    /**
     * @return Collection<\App\Models\Vlan>
     */
    public function discoverBasicVlanData(): Collection;
}
