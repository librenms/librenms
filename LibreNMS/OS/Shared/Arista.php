<?php

/**
 * Arista.php
 *
 * Arista OS group
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

namespace LibreNMS\OS\Shared;

use App\Models\EntPhysical;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\Bgp4V2Mib;
use LibreNMS\Util\Oid;
use LibreNMS\Util\RouteDistinguisher;
use SnmpQuery;

class Arista extends OS implements BgpAddressFamilyDiscovery
{
    use Bgp4V2Mib;

    /** @var array{array<int, array<string, mixed>>, array<int, array<string, mixed>>}|null */
    private ?array $opticalLaneInventory = null;

    public function getOpticalLaneLabel(string $description, int $sensorIndex): string
    {
        if ($this->opticalLaneInventory === null) {
            // Reuse stored discovery inventory; do not add SNMP walks per sensor.
            $entities = [];
            $ports = [];
            $inventory = EntPhysical::where('device_id', $this->getDeviceId())
                ->get(['entPhysicalIndex', 'entPhysicalContainedIn', 'entPhysicalParentRelPos', 'entPhysicalClass', 'entPhysicalName', 'entPhysicalDescr']);
            if ($inventory->count() === $inventory->pluck('entPhysicalIndex')->unique()->count()) {
                $entities = $inventory->keyBy('entPhysicalIndex')->map(fn ($entity) => $entity->getAttributes())->all();
                $ports = Port::where('device_id', $this->getDeviceId())->where('deleted', 0)
                    ->get(['ifName', 'ifIndex', 'ifSpeed'])->map(fn ($port) => $port->getAttributes())->all();
            }
            $this->opticalLaneInventory = [$entities, $ports];
        }

        return self::rewriteOpticalLaneLabel($description, $sensorIndex, ...$this->opticalLaneInventory);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entities  Physical inventory keyed by ENTITY-MIB index.
     * @param  array<int, array<string, mixed>>  $ports  Active logical interface inventory.
     */
    public static function rewriteOpticalLaneLabel(string $description, int $sensorIndex, array $entities, array $ports): string
    {
        if (! preg_match('/^DOM (RX|TX) Power\s+For (Ethernet[0-9]+)\/[0-9]+(?: \(optical lane [1-4]\))?$/i', $description, $match)) {
            return $description;
        }
        $visited = []; $lane = null; $module = null; $parent = null;
        for ($index = $sensorIndex; $index > 0;) {
            if (isset($visited[$index]) || ! isset($entities[$index])) {
                return $description;
            }
            $visited[$index] = true;
            $entity = $entities[$index];
            if (($entity['entPhysicalClass'] ?? null) === 'module'
                && preg_match('/^Lane [0-9]+ for Xcvr for Ethernet[0-9]+$/', $entity['entPhysicalName'] ?? '')) {
                if ($lane !== null || ! isset($entity['entPhysicalParentRelPos']) || ! is_numeric($entity['entPhysicalParentRelPos'])) {
                    return $description;
                }
                $lane = (int) $entity['entPhysicalParentRelPos'];
            }
            if (($entity['entPhysicalClass'] ?? null) === 'container'
                && preg_match('/^Xcvr for (Ethernet[0-9]+):/', $entity['entPhysicalName'] ?? '', $moduleMatch)) {
                if (! str_contains($entity['entPhysicalDescr'] ?? '', '400GBASE-PLR4')) {
                    return $description;
                }
                if ($moduleMatch[1] !== $match[2]) {
                    return $description;
                }
                $module = $index; $parent = $moduleMatch[1];
                break;
            }
            $index = (int) ($entity['entPhysicalContainedIn'] ?? 0);
        }
        if ($module === null || $lane === null || $lane < 0 || $lane > 3) {
            return $description;
        }
        $positions = [];
        foreach ($entities as $entity) {
            if ((int) ($entity['entPhysicalContainedIn'] ?? 0) === $module
                && ($entity['entPhysicalClass'] ?? null) === 'module'
                && preg_match('/^Lane [0-9]+ for Xcvr for ' . preg_quote($parent, '/') . '$/', $entity['entPhysicalName'] ?? '')) {
                $position = $entity['entPhysicalParentRelPos'] ?? null;
                if (! is_numeric($position) || isset($positions[(int) $position])) {
                    return $description;
                }
                $positions[(int) $position] = true;
            }
        }
        foreach ([0, 1, 2, 3] as $required) {
            if (! isset($positions[$required])) {
                return $description;
            }
        }
        $interfaces = []; $indexes = [];
        foreach ($ports as $port) {
            $name = $port['ifName'] ?? '';
            if ($name === $parent) {
                return $description; // Parent and breakout interfaces present: ambiguous.
            }
            if (! str_starts_with($name, $parent . '/')) {
                continue;
            }
            if (! preg_match('/^' . preg_quote($parent, '/') . '\/([0-9]+)$/', $name, $suffix)
                || ! isset($port['ifIndex']) || isset($indexes[$port['ifIndex']])
                || isset($interfaces[(int) $suffix[1]]) || (int) ($port['ifSpeed'] ?? 0) !== 100000000000) {
                return $description;
            }
            $indexes[$port['ifIndex']] = true;
            $interfaces[(int) $suffix[1]] = $name;
        }
        ksort($interfaces);
        if (array_keys($interfaces) !== [1, 3, 5, 7] && array_keys($interfaces) !== [1, 2, 3, 4]) {
            return $description;
        }
        $logical = array_values($interfaces)[$lane];

        return 'DOM ' . strtoupper($match[1]) . ' Power For ' . $logical . ' (optical lane ' . ($lane + 1) . ')';
    }

    public function discoverBgpPeers(): Collection
    {
        return $this->discoverBgp4V2Peers('ARISTA-BGP4V2-MIB', 'aristaBgp4V2');
    }

    public function discoverBgpAddressFamilies(Collection $peers): Collection
    {
        return $this->discoverBgp4V2AddressFamilies($peers, 'ARISTA-BGP4V2-MIB', 'aristaBgp4V2');
    }

    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'ARISTA-BGP4V2-MIB::aristaBgp4V2PeerLocalAs');
    }

    /**
     * ARISTA-VRF-MIB is indexed by the length prefixed VRF name
     */
    public function discoverVrfs(): Collection
    {
        $rds = SnmpQuery::hideMib()->numericIndex()->walk('ARISTA-VRF-MIB::aristaVrfRouteDistinguisher')->throwIfConnectionError()->pluck('aristaVrfRouteDistinguisher');

        return collect($rds)->map(fn ($rd, $index) => new Vrf([
            'vrf_oid' => (string) $index,
            'vrf_name' => Oid::stringFromOid((string) $index),
            'mplsVpnVrfRouteDistinguisher' => RouteDistinguisher::parse($rd),
            'mplsVpnVrfDescription' => '',
        ]))->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        $vrfOids = $vrfs->pluck('vrf_oid', 'vrf_name');
        $members = array_map(fn ($name) => $vrfOids->get($name, ''), SnmpQuery::hideMib()->walk('ARISTA-VRF-MIB::aristaVrfIfMembership')->throwIfConnectionError()->pluck('aristaVrfIfMembership'));

        return $this->vrfMemberPorts($members, $vrfs);
    }
}
