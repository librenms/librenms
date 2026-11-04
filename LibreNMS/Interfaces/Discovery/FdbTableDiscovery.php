<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface FdbTableDiscovery
{
    /**
     * Discover the forwarding database of the device.
     * The OS base class reads Q-BRIDGE-MIB and BRIDGE-MIB. Devices with a vendor MIB should override this and
     * call the parent when it returns no data. Devices with a quirky standard MIB should override fdbBridgePorts()
     * to fix how the table is read, or fdbPortId() to fix how bridge ports are resolved.
     * The vlan attribute is the VLAN number, or 0 when the device doesn't report it.
     *
     * @return Collection<int, \App\Models\PortsFdb>
     */
    public function discoverFdbTable(): Collection;
}
