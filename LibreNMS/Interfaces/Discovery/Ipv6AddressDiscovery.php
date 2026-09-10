<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface Ipv6AddressDiscovery
{
    /**
     * Discover a Collection of Ipv6Address models.
     * Will be keyed by ip, port_id and context_name
     * ipv6_network_id is optional and will be filled by the module
     * ::1 will be filtered by the module as well
     *
     * @return \Illuminate\Support\Collection<\App\Models\EntPhysical>
     */
    public function discoverIpv6Addresses(): Collection;
}
