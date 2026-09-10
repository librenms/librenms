<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface Ipv4AddressDiscovery
{
    /**
     * Discover a Collection of Ipv4Address models.
     * Will be keyed by ip, port_id and context_name
     * ipv4_network_id is optional and will be filled by the module
     * ::1 will be filtered by the module as well
     *
     * @return \Illuminate\Support\Collection<\App\Models\Ipv4Address>
     */
    public function discoverIpv4Addresses(): Collection;
}
