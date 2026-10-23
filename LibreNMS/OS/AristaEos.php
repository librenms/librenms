<?php

/**
 * AristaEos.php
 *
 * -Description-
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
 * @copyright  2026 Dr. Walter Snyder
 */

namespace LibreNMS\OS;

use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class AristaEos extends OS implements TransceiverDiscovery
{
    /**
     * @return Collection<int, Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        $snmpData = SnmpQuery::cache()
            ->hideMib()
            ->walk('ENTITY-MIB::entPhysicalTable')
            ->table(1);

        if (empty($snmpData)) {
            return new Collection;
        }

        $ifIndexMap = $this->getIfIndexEntPhysicalMap();
        $ports = $this->getDevice()
            ->ports()
            ->get(['port_id', 'ifIndex', 'ifName', 'ifDescr']);
        $ifIndexToPortId = $ports->pluck('port_id', 'ifIndex');
        $ifNameToPortId = $ports->pluck('port_id', 'ifName')
            ->union($ports->pluck('port_id', 'ifDescr'));

        return collect($snmpData)
            ->filter(fn (array $ent) => ! empty($ent['entPhysicalModelName'])
                && str_starts_with($ent['entPhysicalDescr'] ?? '', 'Xcvr for '))
            ->map(function (array $ent, $index) use ($ifIndexMap, $ifIndexToPortId, $ifNameToPortId) {
                $containedIn = $ent['entPhysicalContainedIn'] ?? 0;
                $ifIndex = $ifIndexMap[$index] ?? $ifIndexMap[$containedIn] ?? null;
                $portId = $ifIndex ? $ifIndexToPortId->get($ifIndex) : null;

                if (! $portId) {
                    $descr = (string) ($ent['entPhysicalDescr'] ?? '');
                    $portName = ! empty($ent['entPhysicalAlias'])
                        ? $ent['entPhysicalAlias']
                        : (string) preg_replace('/^Xcvr for ([^:\s]+).*$/', '$1', $descr);

                    $portId = $ifNameToPortId->get($portName)
                        ?? $ifNameToPortId->get($portName . '/1');
                }

                if (! $portId) {
                    return null;
                }

                return new Transceiver([
                    'port_id' => (int) $portId,
                    'index' => (string) $index,
                    'type' => $ent['entPhysicalDescr'] ?? null,
                    'vendor' => $ent['entPhysicalMfgName'] ?? null,
                    'revision' => $ent['entPhysicalHardwareRev'] ?? null,
                    'model' => $ent['entPhysicalModelName'] ?? null,
                    'serial' => $ent['entPhysicalSerialNum'] ?? null,
                    'ddm' => true,
                    'entity_physical_index' => (int) $index,
                ]);
            })
            ->filter();
    }
}
