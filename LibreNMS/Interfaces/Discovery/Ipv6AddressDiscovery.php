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
     * A VRF context that did not answer keeps its saved addresses
     *
     * @return \Illuminate\Support\Collection<\App\Models\Ipv6Address>
     *
     * @throws \LibreNMS\Exceptions\SnmpException when the device did not answer, existing addresses are kept
     */
    public function discoverIpv6Addresses(): Collection;
}
