<?php

/**
 * HostRecord.php
 *
 * Everything known about one host, gathered from the address tables
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

namespace App\Http\Controllers\Search\Data;

use App\Models\Device;
use App\Models\Port;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;

class HostRecord
{
    /**
     * @param  list<IP>  $ips
     * @param  list<HostMac>  $macs
     * @param  list<HostLocation>  $locations  closest port first
     * @param  list<NeighborEntry>  $entries  ARP and ND
     * @param  bool  $severalMacs  devices report different MACs for an address in the same context, a device only keeps the latest so this can't tell if they are in use at the same time
     */
    public function __construct(
        public readonly array $ips,
        public readonly array $macs,
        public readonly array $locations,
        public readonly array $entries,
        public readonly ?Port $interface,
        public readonly ?Device $device,
        public readonly bool $severalMacs,
    ) {
    }

    public function isNotSeen(): bool
    {
        return $this->entries === [] && $this->locations === [] && $this->interface === null && $this->device === null;
    }

    /**
     * The first IP, or the first MAC when no IP is known
     */
    public function title(): string
    {
        return ($this->ips[0] ?? null)?->compressed() ?? Mac::parse(($this->macs[0] ?? null)?->hex)->readable();
    }

    public function isMonitored(): bool
    {
        return $this->interface !== null || $this->device !== null;
    }

    public function interfaceDescription(): ?string
    {
        return HostLocation::describePort($this->interface);
    }

    /**
     * The closest port, stale MACs don't count
     */
    public function bestLocation(?string $mac = null): ?HostLocation
    {
        foreach ($this->locations as $location) {
            if ($mac === null ? ! $this->isStale($location->mac) : $location->mac === $mac) {
                return $location;
            }
        }

        return null;
    }

    public function locationState(): LocationState
    {
        return $this->bestLocation()?->state() ?? LocationState::Unknown;
    }

    /**
     * What the list and export show: one row for each MAC when devices report several for an address
     *
     * @return non-empty-list<HostRow>
     */
    public function rows(): array
    {
        if ($this->severalMacs) {
            return array_map(fn (HostMac $mac): HostRow => new HostRow($mac, $this->bestLocation($mac->hex), $mac->seen, $mac->stale), $this->macs) ?: [new HostRow(null, null, null, false)];
        }

        $seen = array_filter(array_column($this->macs, 'seen'));

        return [new HostRow($this->macs[0] ?? null, $this->bestLocation(), $seen ? max($seen) : null, false)];
    }

    /**
     * Current forwarding entries, closest first, one for each port with its VLAN if it has one
     *
     * @return list<HostLocation>
     */
    public function currentLocations(): array
    {
        $byPort = [];
        foreach ($this->locations as $location) {
            if ($this->isStale($location->mac)) {
                continue;
            }
            if (! isset($byPort[$location->portId]) || ($byPort[$location->portId]->vlan === null && $location->vlan !== null)) {
                $byPort[$location->portId] = $location;
            }
        }

        return array_values($byPort);
    }

    /**
     * Forwarding entries on ports other than the best one
     *
     * @return list<HostLocation>
     */
    public function otherLocations(): array
    {
        $best = $this->bestLocation();

        return array_values(array_filter($this->currentLocations(), fn (HostLocation $location) => $location->portId !== $best?->portId));
    }

    /**
     * With no clear port every place it was seen is listed as an equal
     */
    public function hasNoClearPort(): bool
    {
        return $this->locationState() === LocationState::Trunk && ! $this->severalMacs && ! $this->interface;
    }

    /**
     * Forwarding entries to list apart from where the host is
     *
     * @return list<HostLocation>
     */
    public function alsoLearnedOn(): array
    {
        return $this->interface ? $this->currentLocations() : $this->otherLocations();
    }

    public function showsAlsoLearned(): bool
    {
        return $this->alsoLearnedOn() !== [] && ! $this->severalMacs && ! $this->hasNoClearPort();
    }

    /**
     * ARP and ND entries for each interface, the first interface first
     *
     * @return list<list<NeighborEntry>>
     */
    public function neighborInterfaces(): array
    {
        $byPort = [];
        foreach ($this->entries as $entry) {
            $byPort[$entry->port->port_id ?? 0][$entry->key()] = $entry;
        }

        return array_map(array_values(...), array_values($byPort));
    }

    /**
     * The interface that knows a host when nothing says where it is: not monitored and not in a forwarding table
     */
    public function viaPort(): ?Port
    {
        return $this->isMonitored() ? null : ($this->entries[0]->port ?? null);
    }

    public function isStale(string $mac): bool
    {
        foreach ($this->macs as $entry) {
            if ($entry->hex === $mac) {
                return $entry->stale;
            }
        }

        return false;
    }
}
