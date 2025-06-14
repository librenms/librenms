<?php

namespace LibreNMS\Interfaces\Discovery;

interface VlanDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\App\Vlan && LibreNMS\App\PortVlan objects
     *
     * @return array vlanData
     */
    public function discoverVlans($dot1dBasePortIfIndex);
}
