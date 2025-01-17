<?php

namespace LibreNMS\Cache;

class Port
{
    /** @var \App\Models\Port[] */
    private array $ports = [];

    /** @var array<int, int> */
    private array $ifIndexMaps = [];

    public function get(int $port_id): ?\App\Models\Port
    {
        if (! array_key_exists($port_id, $this->ports)) {
            $this->cachePort($port_id);
        }

        return $this->ports[$port_id];
    }

    public function getByIfIndex(\App\Models\Device|int $device, int $ifIndex): \App\Models\Port
    {
        return $this->get((int) $this->getIdFromIfIndex($device, $ifIndex)); // not found null cast to 0
    }

    public function getIdFromIfIndex(\App\Models\Device|int $device, int $ifIndex): ?int
    {
        $device_id = $device instanceof \App\Models\Device ? $device->device_id : $device;

        if (! array_key_exists($device_id, $this->ifIndexMaps)) {
            $this->ifIndexMaps[$device_id] = \App\Models\Port::where('device_id', $device_id)->pluck('port_id', 'ifIndex');
        }

        return $this->ifIndexMaps[$device_id][$ifIndex] ?? null;
    }

    private function cachePort(int $port_id): void
    {
        // save work if port_id is invalid
        if ($port_id == 0) {
            $this->ports[0] = null;

            return;
        }

        // check if the primary device has the ports relationship loaded and try to get the port from there
        $primaryDevice = \DeviceCache::getPrimary();
        if ($primaryDevice->relationLoaded('ports')) {
            $port = $primaryDevice->ports->firstWhere('port_id', $port_id);
            if ($port !== null) {
                $this->ports[$port_id] = $port; // cache the port here

                return; // early return to skip DB query
            }
        }

        // not found any other way, resort to db query
        $this->ports[$port_id] = \App\Models\Port::find($port_id);
    }
}
