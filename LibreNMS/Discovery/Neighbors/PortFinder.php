<?php

/**
 * PortFinder.php
 *
 * Find a port on a single device from the identifiers a neighbor advertises.
 * A match only counts if exactly one port matches, ambiguous matches are skipped.
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

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use Illuminate\Support\Collection;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Util\IP;

class PortFinder
{
    /**
     * @param  Collection<int, Port>  $ports  ports of a single device
     */
    public function __construct(
        private readonly Collection $ports,
    ) {
    }

    public static function forDevice(Device|int $device): self
    {
        return new self(Port::query()
            ->where('device_id', $device instanceof Device ? $device->device_id : $device)
            ->isNotDeleted()
            ->get(['port_id', 'device_id', 'ifIndex', 'ifName', 'ifDescr', 'ifAlias', 'ifPhysAddress']));
    }

    /**
     * Find the port a neighbor is advertising.
     * The port id is tried first as it is the most specific, then the port description, then the port mac.
     * Numeric port ids are frequently not an ifIndex, so they are tried last.
     */
    public function find(Neighbor $neighbor): ?Port
    {
        return $this->byPortId($neighbor)
            ?? $this->byDescr($neighbor->portDescr)
            ?? $this->byMac($neighbor->portMac)
            ?? (ctype_digit($neighbor->portId) ? $this->byIfIndex($neighbor->portId) : null);
    }

    /**
     * Match ifName, then ifDescr
     */
    public function byName(?string $name): ?Port
    {
        return $this->unique('ifName', $name) ?? $this->unique('ifDescr', $name);
    }

    /**
     * Match ifDescr, then ifName, then ifAlias.
     * ifAlias is last because it is user editable, but some bad LLDP implementations use it.
     */
    public function byDescr(?string $descr): ?Port
    {
        return $this->unique('ifDescr', $descr) ?? $this->unique('ifName', $descr) ?? $this->unique('ifAlias', $descr);
    }

    public function byAlias(?string $alias): ?Port
    {
        return $this->unique('ifAlias', $alias);
    }

    public function byIfIndex(int|string|null $ifIndex): ?Port
    {
        if ($ifIndex === null || $ifIndex === '' || ! is_numeric($ifIndex)) {
            return null;
        }

        return $this->ports->firstWhere('ifIndex', (int) $ifIndex);
    }

    public function byMac(?string $mac): ?Port
    {
        return $this->unique('ifPhysAddress', Neighbor::parseMac($mac));
    }

    public function byIp(?string $ip): ?Port
    {
        $ip = $ip === null ? null : IP::parse($ip, true);

        if ($ip === null || $this->ports->isEmpty()) {
            return null;
        }

        $query = $ip->getFamily() == 'ipv4'
            ? Ipv4Address::query()->where('ipv4_address', $ip->uncompressed())
            : Ipv6Address::query()->where('ipv6_address', $ip->uncompressed());

        $port_ids = $query->whereIn('port_id', $this->ports->pluck('port_id'))->distinct()->limit(2)->pluck('port_id');

        return $port_ids->count() == 1 ? $this->ports->firstWhere('port_id', $port_ids->first()) : null;
    }

    private function byPortId(Neighbor $neighbor): ?Port
    {
        if ($neighbor->portId === '') {
            return null;
        }

        return match ($neighbor->portIdSubtype) {
            LldpPortIdSubtype::MacAddress => $this->byMac($neighbor->portMac),
            LldpPortIdSubtype::NetworkAddress => $this->byIp($neighbor->portId),
            LldpPortIdSubtype::InterfaceAlias => $this->byAlias($neighbor->portId) ?? $this->byName($neighbor->portId),
            default => $this->byName($neighbor->portId),
        };
    }

    /**
     * Find the only port where the field matches the value (case-insensitive)
     */
    private function unique(string $field, ?string $value): ?Port
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // use raw attributes to skip accessors (ifPhysAddress)
        $matches = $this->ports->filter(fn (Port $port) => strcasecmp(trim((string) ($port->getAttributes()[$field] ?? '')), $value) === 0);

        return $matches->count() === 1 ? $matches->first() : null;
    }
}
