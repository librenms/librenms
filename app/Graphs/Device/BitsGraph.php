<?php

namespace App\Graphs\Device;

use App\Facades\LibrenmsConfig;
use App\Graphs\Port\StacksPortTraffic;
use App\Models\Port;

/**
 * Traffic of all ports of a device, each port stacked on the others
 */
class BitsGraph extends DeviceGraph
{
    use StacksPortTraffic;

    public function getTitle(): string
    {
        return $this->device()->display . '::bits';
    }

    public function getSeries(): array
    {
        $device = $this->device();
        $ports = $device->ports()->isNotDeleted()->where('disabled', 0)->orderBy('ifIndex')->get()
            ->each(fn (Port $port) => $port->setRelation('device', $device))
            ->filter($this->isTraffic(...));

        return $this->portTrafficSeries($ports, fn (Port $port) => $port->getShortLabel());
    }

    /**
     * Whether the port counts toward the traffic of the device, configured by device_traffic_iftype and device_traffic_descr
     */
    private function isTraffic(Port $port): bool
    {
        // ASA (at least in multicontext) reports layer 3 interfaces as l2vlan, or as propVirtual in 9.16 (like etherchannels),
        // but not the physical ones. Filtering those would leave the graph empty.
        $asaTypes = $port->device?->os === 'asa' ? ['/virtual/', '/l2vlan/'] : [];

        $ignoredType = array_any((array) LibrenmsConfig::get('device_traffic_iftype', []),
            fn ($type) => ! in_array($type, $asaTypes) && preg_match($type . 'i', (string) $port->ifType));
        $ignoredDescr = array_any((array) LibrenmsConfig::get('device_traffic_descr', []),
            fn ($descr) => preg_match($descr . 'i', (string) $port->ifDescr) || preg_match($descr . 'i', (string) $port->ifName));

        return ! $ignoredType && ! $ignoredDescr;
    }
}
