<?php

/**
 * Netgear.php
 *
 * Netgear switches
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

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Port;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\PortFinder;
use LibreNMS\Interfaces\Discovery\NeighborPortResolution;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Netgear extends OS implements NeighborPortResolution, TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::walk('NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoTable')->mapTable(function ($data, $ifIndex) {
            $type = $this->cleanString($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoCompliance'] ?? null);
            // link lengths are the raw SFF-8472 values, in units of 10 m
            $length50 = (int) ($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoLinkLength50um'] ?? 0);
            $length62 = (int) ($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoLinkLength62dot5um'] ?? 0);

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $type,
                'vendor' => $this->cleanString($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoVendorName'] ?? null),
                'model' => $this->cleanString($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoPartNumber'] ?? null),
                'serial' => $this->cleanString($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoSerialNumber'] ?? null),
                'revision' => $this->cleanString($data['NETGEAR-BOXSERVICES-PRIVATE-MIB::boxServicesFiberPortsOpticsInfoRevision'] ?? null),
                'cable' => $this->transceiverCable($type),
                'distance' => $length50 > 0 ? $length50 * 10 : ($length62 > 0 ? $length62 * 10 : null),
            ]);
        });
    }

    private function cleanString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function transceiverCable(?string $type): ?string
    {
        return match (true) {
            $type === null || $type === '' => null,
            (bool) preg_match('/DAC|CR|BASE-?T\b/i', $type) => 'Copper',
            (bool) preg_match('/LRM|SR|SX/i', $type) => 'MM',
            (bool) preg_match('/LR|ER|ZR|LX|BX/i', $type) => 'SM',
            default => null,
        };
    }

    /**
     * Some Netgear switches (GS108Tv1) advertise port "g1", but name the port "Port 1 Gigabit Ethernet", map it to the ifIndex
     */
    public function findNeighborPort(Neighbor $neighbor, PortFinder $ports): ?Port
    {
        if ($this->getDevice()->sysDescr == 'GS108T'
            && $neighbor->sysDescr == 'Smart Switch'
            && preg_match('/^g(\d+)$/', $neighbor->portId, $matches)) {
            return $ports->byIfIndex($matches[1]);
        }

        return null;
    }
}
