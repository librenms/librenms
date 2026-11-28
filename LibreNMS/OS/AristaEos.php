<?php

/**
 * AristaEos.php
 *
 * Arista EOS
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
 * @copyright  2026 Dr. Walter Snyder
 */

namespace LibreNMS\OS;

use App\Models\Sensor;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

class AristaEos extends Shared\Arista implements TransceiverDiscovery
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
            ->filter(fn (array $ent, $sensorIdx) => str_starts_with((string) ($ent['entPhysicalDescr'] ?? ''), 'DOM ')
                && (empty($sensorValues) || ! in_array((string) ($sensorValues[$sensorIdx]['entPhySensorValue'] ?? '-1000000000'), ['-1000000000', '-2147483648', '0'], true)))
            ->mapWithKeys(function (array $ent, $sensorIdx) use ($snmpData) {
                $xcvrIndex = $this->findTransceiverEntityIndex($sensorIdx, $snmpData);

                return $xcvrIndex !== null ? [$xcvrIndex => true] : [];
            });

        return collect($snmpData)
            ->filter(fn (array $ent) => ! empty($ent['entPhysicalModelName'])
                && str_starts_with((string) ($ent['entPhysicalDescr'] ?? ''), 'Xcvr for '))
            ->map(function (array $ent, $index) use ($ports, $ddmTransceivers) {
                $descr = (string) ($ent['entPhysicalDescr'] ?? '');
                $candidates = array_filter(array_unique([
                    ! empty($ent['entPhysicalAlias']) ? (string) $ent['entPhysicalAlias'] : null,
                    (string) preg_replace('/^Xcvr for ([^:\s]+).*$/', '$1', $descr),
                ]));

                $port = null;
                foreach ($candidates as $alias) {
                    $port = $ports
                        ->filter(fn ($p) => $p->ifName === $alias
                            || str_starts_with((string) $p->ifName, $alias . '/')
                            || $p->ifDescr === $alias
                            || str_starts_with((string) $p->ifDescr, $alias . '/'))
                        ->sortBy('ifIndex')
                        ->first();
                    if ($port) {
                        break;
                    }
                }

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

    /**
     * Unnamed entities get a name from their description and the unit they belong to
     */
    protected function entitySensorDescr(int $index, array $entities): string
    {
        $descr = parent::entitySensorDescr($index, $entities);
        if ($descr !== '') {
            return $descr;
        }

        $descr = $entities[$index]['entPhysicalDescr'] ?? '';
        if (preg_match('/(Input|Output) (voltage|current) sensor/i', $descr) || preg_match('/^(Power Supply|Hotspot|Inlet|Board)/i', $descr)) {
            $unit = $entities[substr_replace((string) $index, '000', -3)]['entPhysicalDescr'] ?? '';
            $descr = ucwords($unit) . ' ' . preg_replace('/(Voltage|Current|Power Supply) Sensor$/i', '', ucwords($descr));
        }

        return preg_replace('/(temp|temperature) sensor$/i', '', $descr);
    }

    /**
     * DOM power is converted to dBm, limits come from ARISTA-ENTITY-SENSOR-MIB and sensors are grouped
     */
    protected function entitySensor(Sensor $sensor, array $entry): ?Sensor
    {
        // DOM power is reported in 0.1 µW, polling converts it the same way
        $toDbm = fn ($value) => UserFuncHelper::apply('uw_to_dbm', $value / 10);
        $limitDbm = fn ($value) => is_numeric($dbmValue = $toDbm($value)) ? round($dbmValue, 2) : null;
        $dbm = $sensor->sensor_class == 'power' && preg_match('/DOM [RT]x Power/i', $sensor->sensor_descr);

        if ($dbm) {
            $sensor->fill([
                'sensor_class' => 'dbm',
                'sensor_current' => $toDbm($entry['entPhySensorValue'] ?? 0),
                'sensor_divisor' => 10,
                'sensor_multiplier' => 1,
                'user_func' => 'uw_to_dbm',
            ]);
        }

        $thresholds = SnmpQuery::cache()->hideMib()->walk('ARISTA-ENTITY-SENSOR-MIB::aristaEntSensorThresholdTable')->table(1)[$sensor->sensor_index] ?? [];
        foreach ([
            'sensor_limit_low_warn' => 'aristaEntSensorThresholdLowWarning',
            'sensor_limit_low' => 'aristaEntSensorThresholdLowCritical',
            'sensor_limit_warn' => 'aristaEntSensorThresholdHighWarning',
            'sensor_limit' => 'aristaEntSensorThresholdHighCritical',
        ] as $limit => $oid) {
            $value = $thresholds[$oid] ?? null;
            if (is_numeric($value) && abs($value) != 1000000000) {
                $sensor->$limit = $dbm ? $limitDbm($value) : $value * $sensor->sensor_multiplier / $sensor->sensor_divisor;
            }
        }

        $descr = $sensor->sensor_descr;
        if (preg_match('/DOM /i', $descr)) {
            $sensor->group = 'SFPs';
            $entities = SnmpQuery::cache()->hideMib()->walk([
                'ENTITY-MIB::entPhysicalDescr',
                'ENTITY-MIB::entPhysicalContainedIn',
            ])->table(1);
            $xcvrIndex = $this->findTransceiverEntityIndex($sensor->sensor_index, $entities);
            if ($xcvrIndex !== null) {
                $sensor->entPhysicalIndex = (string) $xcvrIndex;
                $sensor->group = 'transceiver';
            }
        } elseif (preg_match('/PwrCon/', $descr)) {
            $unit = explode(' ', $descr)[0];
            $sensor->group = preg_match('/PwrCon[0-9]/', $unit) ? $unit : preg_replace('/PwrCon/i', '', $unit);
            $sensor->sensor_descr = preg_replace('/^.*?(PwrCon)[0-9]*/i', '', $descr);
        } elseif (preg_match('/^(Trident.*|Jericho[0-9]|FM6000)/i', $descr)) {
            $sensor->group = 'Platform';
        } elseif (preg_match('/^(Power|PSU)/i', $descr)) {
            $sensor->group = 'PSUs';
        } else {
            $sensor->group = 'System';
            $sensor->sensor_descr = Str::replaceLast('temp sensor', '', $descr);
        }

        return $sensor;
    }

    /**
     * Walk up ENTITY-MIB containment to find the parent transceiver entity index.
     *
     * @param  array<int|string, array<string, mixed>>  $entities
     */
    private function findTransceiverEntityIndex(int|string|null $index, array $entities): ?int
    {
        if ($index === null) {
            return null;
        }

        $parent = $entities[$index]['entPhysicalContainedIn'] ?? null;
        $visited = [];
        while (is_numeric($parent)) {
            $parentId = (int) $parent;
            if ($parentId <= 0 || ! isset($entities[$parentId]) || isset($visited[$parentId])) {
                break;
            }
            $visited[$parentId] = true;
            if (str_starts_with((string) ($entities[$parentId]['entPhysicalDescr'] ?? ''), 'Xcvr for ')) {
                return $parentId;
            }
            $parent = $entities[$parentId]['entPhysicalContainedIn'] ?? null;
        }

        return null;
    }
}
