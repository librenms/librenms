<?php

/**
 * CiscoCdpMib.php
 *
 * CDP neighbor discovery via CISCO-CDP-MIB
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

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Util\IP;
use SnmpQuery;

trait CiscoCdpMib
{
    /**
     * @return Collection<int, Neighbor>
     */
    protected function discoverCdpNeighbors(): Collection
    {
        return SnmpQuery::hideMib()->walk('CISCO-CDP-MIB::cdpCacheTable')
            ->mapTable(fn (array $entry, $ifIndex, $deviceIndex = null) => $deviceIndex === null ? null : new Neighbor(
                protocol: 'cdp',
                localPortId: PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()),
                sysName: self::cdpDeviceName($entry['cdpCacheDeviceId'] ?? ''),
                sysDescr: Neighbor::parseText($entry['cdpCacheVersion'] ?? ''),
                platform: Neighbor::parseText($entry['cdpCachePlatform'] ?? ''),
                managementIp: IP::fromHexString($entry['cdpCacheAddress'] ?? '', true)?->compressed(),
                portId: Neighbor::parseText($entry['cdpCacheDevicePort'] ?? ''),
            ))
            ->filter(fn (?Neighbor $neighbor) => $neighbor !== null && $neighbor->sysName !== '' && $neighbor->portId !== '')
            ->values();
    }

    /**
     * Some devices (NX-OS) append their serial number in parentheses to the device id
     */
    private static function cdpDeviceName(mixed $deviceId): string
    {
        return trim((string) preg_replace('/\([^)]*\)$/', '', Neighbor::parseName($deviceId)));
    }
}
