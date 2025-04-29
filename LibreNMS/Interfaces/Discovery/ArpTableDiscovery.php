<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface ArpTableDiscovery
{
    /**
     * @return Collection<\App\Models\Ipv4Mac>
     */
    public function discoverArpTable(): Collection;
}
