<?php

/**
 * NeighborFinder.php
 *
 * Find the device and port a discovery protocol neighbor refers to.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Neighbors;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Discovery\NeighborPortResolution;
use LibreNMS\OS;
use LibreNMS\Util\IP;
use LibreNMS\Util\Validate;

class NeighborFinder
{
    /** @var array<int, PortFinder> */
    private array $portFinders = [];
    /** @var array<int, OS> */
    private array $os = [];
    /** @var array<string, int|null> device_id by neighbor identifiers, many neighbors are seen more than once */
    private array $deviceIds = [];
    /** @var array<string, array{all: int[], direct: int[]}> */
    private array $ipOwners = [];

    /**
     * Find a known device, trying the most reliable identifiers first:
     *  1. hostname: what the device was added to LibreNMS as
     *  2. management ip: the device's ip or an ip assigned to one of its ports
     *  3. port mac, then chassis mac: a mac assigned to one of the device's ports
     *  4. sysName: names are frequently reused or left at defaults, so this is the weakest
     * Ambiguous IPs restrict MAC/sysName matching to the address owners. Only MAC-less
     * CDP/FDP retain the unique direct management match when those names cannot resolve.
     */
    public function findDevice(Neighbor $neighbor): ?Device
    {
        $key = implode('|', [$neighbor->protocol, $neighbor->sysName, $neighbor->managementIp, $neighbor->portMac, $neighbor->chassisMac]);

        if (! array_key_exists($key, $this->deviceIds)) {
            $nameIp = IP::parse($neighbor->sysName, true);
            $ambiguousAddressName = $nameIp !== null
                && $nameIp->compressed() === IP::parse($neighbor->managementIp ?? '', true)?->compressed()
                && $this->hasAmbiguousIp($neighbor);

            // Repeating a shared management IP as a name adds no independent identity.
            $this->deviceIds[$key] = ($ambiguousAddressName ? null : $this->byHostname($neighbor->sysName))
                ?? $this->byNeighborIdentifiers($neighbor, $ambiguousAddressName);
        }

        $device_id = $this->deviceIds[$key];

        return $device_id ? DeviceCache::get($device_id) : null;
    }

    public function hasAmbiguousIp(Neighbor $neighbor): bool
    {
        return count($this->ipCandidates($neighbor->managementIp)['all']) > 1;
    }

    private function byNeighborIdentifiers(Neighbor $neighbor, bool $ambiguousAddressName): ?int
    {
        $owners = $this->ipCandidates($neighbor->managementIp);
        if (count($owners['all']) === 1) {
            return $owners['all'][0];
        }

        $candidates = $owners['all'] ?: null;
        $match = $this->byMac($neighbor->portMac, $candidates)
            ?? $this->byMac($neighbor->chassisMac, $candidates)
            ?? ($ambiguousAddressName ? null : $this->bySysName($neighbor->sysName, $candidates));

        // CDP/FDP parsers generally have only a management IP and a device name.
        // Preserve their direct-address fallback, but never apply it to LLDP or
        // ignore advertised MAC evidence that failed to identify an IP owner.
        if ($match === null && in_array($neighbor->protocol, ['cdp', 'fdp'], true)
            && $neighbor->portMac === null && $neighbor->chassisMac === null
            && count($owners['direct']) === 1) {
            return $owners['direct'][0];
        }

        return $match;
    }

    /**
     * Find the neighbor's port on the given device.
     * The device's OS may resolve its own port names, otherwise generic matching is used.
     */
    public function findPort(Neighbor $neighbor, Device $device): ?Port
    {
        $ports = $this->portFinders[$device->device_id] ??= PortFinder::forDevice($device);
        $os = $this->os($device);

        if ($os instanceof NeighborPortResolution) {
            $port = $os->findNeighborPort($neighbor, $ports);

            if ($port !== null) {
                return $port;
            }
        }

        return $ports->find($neighbor);
    }

    private function byHostname(string $name): ?int
    {
        if (! Validate::hostname($name)) {
            return null;
        }

        // the exact name first, then with or without the configured domain
        foreach ($this->nameVariants($name) as $hostname) {
            $device_ids = Device::query()->where('hostname', $hostname)->limit(2)->pluck('device_id');

            if ($device_ids->count() == 1) {
                return $device_ids->first();
            }
        }

        return null;
    }

    /** @param int[]|null $candidates */
    private function bySysName(string $name, ?array $candidates = null): ?int
    {
        if ($name === '') {
            return null;
        }

        $device_ids = Device::query()->whereIn('sysName', $this->nameVariants($name))
            ->when($candidates !== null, fn ($query) => $query->whereIn('device_id', $candidates))
            ->limit(2)->pluck('device_id');

        if ($device_ids->count() > 1) {
            Log::debug("More than one device found with sysName '$name'");

            return null;
        }

        return $device_ids->first();
    }

    /** @return array{all: int[], direct: int[]} */
    private function ipCandidates(?string $ip): array
    {
        $ip = $ip === null ? null : IP::parse($ip, true);

        if ($ip === null) {
            return ['all' => [], 'direct' => []];
        }

        $key = $ip->compressed();
        if (isset($this->ipOwners[$key])) {
            return $this->ipOwners[$key];
        }

        $relation = $ip->getFamily() == 'ipv4' ? 'ipv4' : 'ipv6';
        $port_devices = Port::query()->selectRaw('ports.device_id, 0 AS direct')
            ->join("{$relation}_addresses", 'ports.port_id', '=', "{$relation}_addresses.port_id")
            ->where("{$relation}_address", $ip->uncompressed())
            ->isNotDeleted()->distinct();

        // Keep every distinct owner so a third candidate can still be disambiguated.
        $owners = Device::query()->selectRaw('device_id, 1 AS direct')
            ->where(fn ($query) => $query
                ->whereIn('hostname', [$ip->compressed(), $ip->uncompressed()])
                ->orWhere('ip', $ip->packed()))
            ->union($port_devices)->toBase()->get();

        return $this->ipOwners[$key] = [
            'all' => $owners->pluck('device_id')->unique()->values()->all(),
            'direct' => $owners->where('direct', 1)->pluck('device_id')->all(),
        ];
    }

    /**
     * Find a device with a port that has this mac, only if a single device has it
     */
    private function byMac(?string $mac, ?array $candidates = null): ?int
    {
        if ($mac === null) {
            return null;
        }

        $device_ids = Port::query()->where('ifPhysAddress', $mac)->isNotDeleted()
            ->when($candidates !== null, fn ($query) => $query->whereIn('device_id', $candidates))
            ->distinct()->limit(2)->pluck('device_id');

        return $device_ids->count() == 1 ? $device_ids->first() : null;
    }

    /**
     * The name and the name with or without the configured domain
     *
     * @return string[]
     */
    private function nameVariants(string $name): array
    {
        $domain = trim((string) LibrenmsConfig::get('mydomain'), '.');

        if ($domain === '') {
            return [$name];
        }

        $suffix = ".$domain";

        if (str_ends_with(strtolower($name), strtolower($suffix))) {
            return [$name, substr($name, 0, -strlen($suffix))];
        }

        return [$name, $name . $suffix];
    }

    private function os(Device $device): OS
    {
        if (! isset($this->os[$device->device_id])) {
            $device_array = $device->toArray();
            $this->os[$device->device_id] = OS::make($device_array);
        }

        return $this->os[$device->device_id];
    }
}
