<?php

/**
 * LldpMib.php
 *
 * LLDP neighbor discovery via LLDP-MIB with a fallback to LLDP-V2-MIB
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
use Illuminate\Support\Str;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Discovery\Neighbors\PortFinder;
use LibreNMS\Util\IP;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

trait LldpMib
{
    private ?PortFinder $lldpLocalPortFinder = null;
    /** @var array<int|string, array<string, mixed>>|null */
    private ?array $lldpLocPortTable = null;

    /**
     * @return Collection<int, Neighbor>
     */
    protected function discoverLldpNeighbors(): Collection
    {
        $neighbors = $this->discoverLldpV1Neighbors();

        return $neighbors->isNotEmpty() ? $neighbors : $this->discoverLldpV2Neighbors();
    }

    /**
     * @return Collection<int, Neighbor>
     */
    protected function discoverLldpV1Neighbors(): Collection
    {
        $remTable = SnmpQuery::hideMib()->cache()->walk('LLDP-MIB::lldpRemTable');

        if (! $remTable->isValid()) {
            return new Collection;
        }

        $addresses = $this->lldpManagementAddresses('.1.0.8802.1.1.2.1.4.2.1.3', 3);

        return $remTable->mapTable(fn (array $entry, $timeMark, $lldpPortNum = null, $remIndex = null) => $remIndex === null ? null : NeighborParser::fromLldpRemEntry(
            $entry,
            $this->lldpLocalPortId((int) $lldpPortNum),
            $addresses["$lldpPortNum.$remIndex"] ?? null,
        ))->filter()->values();
    }

    /**
     * @return Collection<int, Neighbor>
     */
    protected function discoverLldpV2Neighbors(): Collection
    {
        $remTable = SnmpQuery::hideMib()->walk('LLDP-V2-MIB::lldpV2RemTable');

        if (! $remTable->isValid()) {
            return new Collection;
        }

        $addresses = $this->lldpManagementAddresses('.1.3.111.2.802.1.1.13.1.4.2.1.3', 4);

        return $remTable->mapTable(function (array $entry, $timeMark, $ifIndex = null, $destMacIndex = null, $remIndex = null) use ($addresses) {
            if ($remIndex === null) {
                return null; // invalid index
            }

            $v1Entry = [];
            foreach ($entry as $column => $value) {
                $v1Entry[str_replace('lldpV2Rem', 'lldpRem', $column)] = $value;
            }

            // LLDP-V2-MIB ids have a 1x: display hint, so text ids are printed as hex
            foreach (['lldpRemChassisId', 'lldpRemPortId'] as $column) {
                $v1Entry[$column] = self::decodeLldpV2Id((string) ($v1Entry[$column] ?? ''));
            }

            return NeighborParser::fromLldpRemEntry(
                $v1Entry,
                PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()),
                $addresses["$ifIndex.$destMacIndex.$remIndex"] ?? null,
            );
        })->filter()->values();
    }

    /**
     * Decode a hex (1x:) LLDP-V2 id if it is text, binary ids (mac addresses) are left as hex
     */
    private static function decodeLldpV2Id(string $id): string
    {
        if (! StringHelpers::isHex($id, ':')) {
            return $id;
        }

        $bytes = (string) hex2bin(str_replace(':', '', $id));

        return preg_match('/^[\x20-\x7E]+$/', $bytes) ? $bytes : $id;
    }

    /**
     * Map an LLDP port number (lldpRemLocalPortNum) to a port_id on this device.
     * Override if this OS numbers its LLDP ports in some other way.
     */
    protected function lldpLocalPortId(int $lldpPortNum): ?int
    {
        return $this->lldpLocalPorts()->findLldpLocalPort(
            $lldpPortNum,
            $this->lldpLocPortTable()[$lldpPortNum] ?? [],
            $this->lldpBridgePortIfIndexes(),
        )?->port_id;
    }

    /**
     * The standard says lldpRemLocalPortNum is the dot1dBasePort, but many devices use ifIndex.
     * Return an empty array if this OS numbers LLDP ports by ifIndex and its bridge ports would conflict.
     *
     * @return array<int|string, int|string>
     */
    protected function lldpBridgePortIfIndexes(): array
    {
        return $this->bridgePortIfIndexes();
    }

    /**
     * lldpLocPortTable indexed by lldpLocPortNum
     *
     * @return array<int|string, array<string, mixed>>
     */
    protected function lldpLocPortTable(): array
    {
        return $this->lldpLocPortTable ??= SnmpQuery::hideMib()->walk('LLDP-MIB::lldpLocPortTable')->table(1);
    }

    protected function lldpLocalPorts(): PortFinder
    {
        return $this->lldpLocalPortFinder ??= PortFinder::forDevice($this->getDeviceId());
    }

    /**
     * Parse management addresses from the lldp(V2)RemManAddrTable.
     * The address is only available in the index: <rem index>.<address subtype>.<address length>.<address>
     * IPv4 is preferred, if a neighbor advertises several addresses.
     *
     * @param  string  $oid  numeric oid of a column in lldp(V2)RemManAddrTable
     * @param  int  $remIndexLength  number of index parts of the neighbor index (including the leading time mark)
     * @return array<string, string> ip addresses keyed by the neighbor index (without time mark)
     */
    private function lldpManagementAddresses(string $oid, int $remIndexLength): array
    {
        $addresses = [];
        $hasIpv4 = [];

        foreach (SnmpQuery::numeric()->walk($oid)->values() as $key => $value) {
            $index = explode('.', Str::after((string) $key, ltrim($oid, '.') . '.'));
            $remIndex = implode('.', array_slice($index, 1, $remIndexLength - 1));
            $length = (int) ($index[$remIndexLength + 1] ?? 0);
            $address = array_slice($index, $remIndexLength + 2, $length);

            if ($length === 0 || count($address) !== $length) {
                continue;
            }

            $ip = IP::fromSnmpString(implode(' ', $address), true);
            if ($ip === null || isset($hasIpv4[$remIndex])) {
                continue;
            }

            if ($ip->getFamily() == 'ipv4') {
                $hasIpv4[$remIndex] = true;
                $addresses[$remIndex] = $ip->compressed();
            } else {
                $addresses[$remIndex] ??= $ip->compressed();
            }
        }

        return $addresses;
    }
}
