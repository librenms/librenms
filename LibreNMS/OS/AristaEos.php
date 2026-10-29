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

        $ports = $this->getDevice()
            ->ports()
            ->get(['port_id', 'ifIndex', 'ifName', 'ifDescr']);

        $sensorValues = SnmpQuery::cache()
            ->hideMib()
            ->walk('ENTITY-SENSOR-MIB::entPhySensorValue')
            ->table(1);

        $ddmTransceivers = collect($snmpData)
            ->filter(fn (array $ent, $sensorIdx) => str_starts_with($ent['entPhysicalDescr'] ?? '', 'DOM ')
                && (empty($sensorValues) || ! in_array((string) ($sensorValues[$sensorIdx]['entPhySensorValue'] ?? '-1000000000'), ['-1000000000', '-2147483648', '0'], true)))
            ->mapWithKeys(function (array $ent) use ($snmpData) {
                $parent = $ent['entPhysicalContainedIn'] ?? null;
                while ($parent && isset($snmpData[$parent])) {
                    if (str_starts_with($snmpData[$parent]['entPhysicalDescr'] ?? '', 'Xcvr for ')) {
                        return [$parent => true];
                    }
                    $parent = $snmpData[$parent]['entPhysicalContainedIn'] ?? null;
                }

                return [];
            });

        return collect($snmpData)
            ->filter(fn (array $ent) => ! empty($ent['entPhysicalModelName'])
                && str_starts_with($ent['entPhysicalDescr'] ?? '', 'Xcvr for '))
            ->map(function (array $ent, $index) use ($ports, $ddmTransceivers) {
                $descr = (string) ($ent['entPhysicalDescr'] ?? '');
                $alias = ! empty($ent['entPhysicalAlias'])
                    ? (string) $ent['entPhysicalAlias']
                    : (string) preg_replace('/^Xcvr for ([^:\s]+).*$/', '$1', $descr);

                $port = $ports
                    ->filter(fn ($p) => $p->ifName === $alias
                        || str_starts_with((string) $p->ifName, $alias . '/')
                        || $p->ifDescr === $alias
                        || str_starts_with((string) $p->ifDescr, $alias . '/'))
                    ->sortBy('ifIndex')
                    ->first();

                if (! $port) {
                    return null;
                }

                $type = $ent['entPhysicalModelName'] ?? null;
                $cable = null;
                if (preg_match('/^Xcvr for ([^:]+): .* type (\S+) media (.+)$/', $descr, $m)) {
                    $type = $m[3] !== 'Unknown' ? "{$m[2]} {$m[3]}" : $m[2];
                    if (preg_match('/-CR\d*$/', $m[3])) {
                        $cable = 'Copper';
                    }
                }

                return new Transceiver([
                    'port_id' => (int) $port->port_id,
                    'index' => (string) $index,
                    'type' => $type,
                    'vendor' => $ent['entPhysicalMfgName'] ?? null,
                    'revision' => $ent['entPhysicalHardwareRev'] ?? null,
                    'model' => $ent['entPhysicalModelName'] ?? null,
                    'serial' => $ent['entPhysicalSerialNum'] ?? null,
                    'cable' => $cable,
                    'ddm' => $ddmTransceivers->has($index),
                    'entity_physical_index' => (int) $index,
                ]);
            })
            ->filter();
    }
}
