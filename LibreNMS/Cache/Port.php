<?php

namespace LibreNMS\Cache;

use App\Facades\DeviceCache;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

class Port
{
    /** @var \App\Models\Port[] */
    private array $ports = [];

    /** @var array<int, array<int, int>> */
    private array $ifIndexMaps = [];

    /** @var array<int, array<int, string>> */
    private array $ifNameMaps = [];

    /** @var array<int, array<string, array<string, string>>> */
    private array $ipMaps = [];

    /**
     * Get a port by id and cache it so future calls will avoid a db query
     * Tries to check the primary device's port relationship to save a db query
     * returns null when port is not found (including port_id = 0)
     */
    public function get(?int $port_id): ?\App\Models\Port
    {
        if (! $port_id) {
            return null;
        }

        if (! array_key_exists($port_id, $this->ports)) {
            $this->cachePort($port_id);
        }

        return $this->ports[$port_id];
    }

    /**
     * Get a port from an ifIndex.
     * Must be constrained to a device, when $device is null, use primary device
     */
    public function getByIfIndex(int|string|null $ifIndex, \App\Models\Device|int|null $device = null): ?\App\Models\Port
    {
        return $this->get($this->getIdFromIfIndex($ifIndex, $device));
    }

    /**
     * Get a port from an ifName.
     * Must be constrained to a device, when $device is null, use primary device
     */
    public function getByIfName(string $ifName, \App\Models\Device|int|null $device = null): ?\App\Models\Port
    {
        return $this->get($this->getIdFromIfName($ifName, $device));
    }

    /**
     * Get a port from an IP address.
     * Must be constrained to a device, when $device is null, use primary device
     */
    public function getByIp(string|IP $ip, ?string $context_name = null, \App\Models\Device|int|null $device = null): ?\App\Models\Port
    {
        return $this->get($this->getIdFromIp($ip, $context_name, $device));
    }

    /**
     * Get a port_id from an ifIndex.
     * Must be constrained to a device, when $device is null, use primary device
     */
    public function getIdFromIfIndex(int|string|null $ifIndex, \App\Models\Device|int|null $device = null): ?int
    {
        $device_id = $this->deviceToId($device);
        $ifIndex = (int) $ifIndex;

        if (! array_key_exists($device_id, $this->ifIndexMaps)) {
            $this->ifIndexMaps[$device_id] = \App\Models\Port::where('device_id', $device_id)->pluck('port_id', 'ifIndex')->all();
        }

        return $this->ifIndexMaps[$device_id][$ifIndex] ?? null;
    }

    /**
     * Get a port_id from an ifName.
     * Must be constrained to a device, when $device is null, use primary device
     */
    public function getIdFromIfName(string $ifName, \App\Models\Device|int|null $device = null): ?int
    {
        $device_id = $this->deviceToId($device);

        if (! array_key_exists($device_id, $this->ifNameMaps)) {
            $this->ifNameMaps[$device_id] = \App\Models\Port::where('device_id', $device_id)->pluck('port_id', 'ifName')->all();
        }

        if (isset($this->ifNameMaps[$device_id][$ifName])) {
            return (int) $this->ifNameMaps[$device_id][$ifName];
        }

        return null;
    }

    /**
     * Search for a port_id by IP addresses assigned to that port
     * *Note, if $device is null, search all devices.
     */
    public function getIdFromIp(string|IP $ip, ?string $context_name = null, \App\Models\Device|int|null $device = null): ?int
    {
        if (! $ip instanceof IP) {
            try {
                $ip = IP::parse($ip);
            } catch (InvalidIpException $e) {
                Log::debug($e->getMessage());

                return null;
            }
        }

        $device_id = $this->deviceToId($device);
        $ip_string = $ip->uncompressed();
        $context_name = (string) $context_name;

        if (! array_key_exists($device_id, $this->ipMaps) || ! array_key_exists($ip_string, $this->ipMaps[$device_id])) {
            if ($ip->getFamily() == 'ipv4') {
                $query = $device ? DeviceCache::get($device_id)->ipv4() : Ipv4Address::query();
                $this->ipMaps[$device_id][$context_name][$ip_string] = $query
                    ->where('ipv4_address', $ip_string)
                    ->where('context_name', $context_name)
                    ->value('ipv4_addresses.port_id');
            } else {
                $query = $device ? DeviceCache::get($device_id)->ipv6() : Ipv6Address::query();
                $this->ipMaps[$device_id][$context_name][$ip_string] = $query
                    ->where('ipv6_address', $ip_string)
                    ->where('context_name', $context_name)
                    ->value('ipv6_addresses.port_id');
            }
        }

        if (isset($this->ipMaps[$device_id][$context_name][$ip_string])) {
            return (int) $this->ipMaps[$device_id][$context_name][$ip_string];
        }

        return null;
    }

    public function getNameFromIfIndex(int $ifIndex, \App\Models\Device|int|null $device = null): ?string
    {
        $device_id = $this->deviceToId($device);

        if (! array_key_exists($device_id, $this->ifNameMaps)) {
            $this->ifNameMaps[$device_id] = \App\Models\Port::where('device_id', $device_id)->pluck('ifName', 'ifIndex')->all();
        }

        return $this->ifNameMaps[$device_id][$ifIndex] ?? null;
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

    private function deviceToId(\App\Models\Device|int|null $device): int
    {
        if ($device === null) {
            return (int) \DeviceCache::getPrimary()->device_id;
        }

        if ($device instanceof \App\Models\Device) {
            return $device->device_id;
        }

        return $device;
    }
}
