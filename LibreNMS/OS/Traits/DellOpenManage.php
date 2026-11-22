<?php

/**
 * DellOpenManage.php
 *
 * Sensors of servers with Dell OpenManage Server Administrator
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

use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorType;
use SnmpQuery;

trait DellOpenManage
{
    /**
     * Dell OpenManage Server Administrator answers for the chassis of the server
     */
    public function hasDellOpenManage(): bool
    {
        return $this->dellChassis('chassisModelName') !== '' || $this->dellChassis('chassisServiceTagName') !== '';
    }

    /**
     * A value of the main chassis from MIB-Dell-10892::chassisInformationTable
     */
    protected function dellChassis(string $name): string
    {
        return trim((string) SnmpQuery::cache()->mibDir('dell')->get("MIB-Dell-10892::$name.1")->value());
    }

    /**
     * Dell server sensors, from MIB-Dell-10892 and StorageManagement-MIB
     *
     * @return Collection<int, Sensor>
     */
    public function discoverDellOpenManageSensors(): Collection
    {
        $sensors = new Collection;

        $probes = [
            // table, class, reading oid, column prefix, divisor, filter
            ['coolingDeviceTable', SensorType::Fanspeed, '.1.3.6.1.4.1.674.10892.1.700.12.1.6', 'coolingDevice', 1, null],
            ['amperageProbeTable', SensorType::Power, '.1.3.6.1.4.1.674.10892.1.600.30.1.6', 'amperageProbe', 1, fn ($entry) => ($entry['MIB-Dell-10892::amperageProbeType'] ?? null) == 26], // amperageProbeTypeIsSystemWatts
            ['voltageProbeTable', SensorType::Voltage, '.1.3.6.1.4.1.674.10892.1.600.20.1.6', 'voltageProbe', 1000, fn ($entry) => ($entry['MIB-Dell-10892::voltageProbeType'] ?? null) != 16], // voltageProbeTypeIsDiscrete
            ['temperatureProbeTable', SensorType::Temperature, '.1.3.6.1.4.1.674.10892.1.700.20.1.6', 'temperatureProbe', 10, null],
        ];

        foreach ($probes as [$table, $type, $oid, $prefix, $divisor, $filter]) {
            foreach (SnmpQuery::mibDir('dell')->walk("MIB-Dell-10892::$table")->valuesByIndex() as $index => $entry) {
                if ($filter && ! $filter($entry)) {
                    continue;
                }

                $limit = fn (string $name) => is_numeric($value = $entry["MIB-Dell-10892::$prefix$name"] ?? null) ? $value / $divisor : null;
                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $type->value,
                    'sensor_oid' => "$oid.$index",
                    'sensor_index' => $index,
                    'sensor_type' => 'dell',
                    'sensor_descr' => $entry["MIB-Dell-10892::{$prefix}LocationName"] ?? '',
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $limit('Reading'),
                    'sensor_limit_low' => $limit('LowerCriticalThreshold'),
                    'sensor_limit_low_warn' => $limit('LowerNonCriticalThreshold'),
                    'sensor_limit_warn' => $limit('UpperNonCriticalThreshold'),
                    'sensor_limit' => $limit('UpperCriticalThreshold'),
                    'entPhysicalIndex' => $index,
                    'rrd_type' => 'GAUGE',
                ]));
            }
        }

        foreach (self::dellStateTables() as [$table, $oid, $status, $descr, $mib, $states]) {
            $data = SnmpQuery::mibDir('dell')->walk("$mib::$table")->valuesByIndex();
            $names = match ($status) {
                'batteryState' => SnmpQuery::mibDir('dell')->walk("$mib::batteryConnectionControllerName")->pluck(),
                'arrayDiskState' => SnmpQuery::mibDir('dell')->walk("$mib::arrayDiskEnclosureConnectionEnclosureName")->pluck(),
                default => [],
            };

            foreach ($data as $index => $entry) {
                $value = $entry["$mib::$status"] ?? null;
                if ($value === null) {
                    continue; // newer MIBs add rows to some tables
                }

                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'state',
                    'sensor_oid' => "$oid.$index",
                    'sensor_index' => $index,
                    'sensor_type' => "dell.$status",
                    'sensor_descr' => match ($status) {
                        'intrusionStatus' => 'Intrusion',
                        'batteryState', 'arrayDiskState' => ($names[$index] ?? '') . ' - ' . ($entry["$mib::$descr"] ?? ''),
                        default => strip_tags((string) ($entry["$mib::$descr"] ?? '')), // virtualDiskDeviceName is user defined
                    },
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $value,
                    'entPhysicalIndex' => $index,
                    'rrd_type' => 'GAUGE',
                ])->withStates($states));
            }
        }

        return $sensors;
    }

    /**
     * @return list<array{string, string, string, string, string, list<array{value: int, generic: int, descr: string}>}>
     */
    private static function dellStateTables(): array
    {
        $status = [
            ['value' => 1, 'generic' => 3, 'descr' => 'other'],
            ['value' => 2, 'generic' => 3, 'descr' => 'unknown'],
            ['value' => 3, 'generic' => 0, 'descr' => 'ok'],
            ['value' => 4, 'generic' => 1, 'descr' => 'nonCritical'],
            ['value' => 5, 'generic' => 2, 'descr' => 'critical'],
            ['value' => 6, 'generic' => 2, 'descr' => 'nonRecoverable'],
        ];

        return [
            ['processorDeviceStatusTable', '.1.3.6.1.4.1.674.10892.1.1100.32.1.5', 'processorDeviceStatusStatus', 'processorDeviceStatusLocationName', 'MIB-Dell-10892', $status],
            ['memoryDeviceTable', '.1.3.6.1.4.1.674.10892.1.1100.50.1.5', 'memoryDeviceStatus', 'memoryDeviceLocationName', 'MIB-Dell-10892', $status],
            ['powerSupplyTable', '.1.3.6.1.4.1.674.10892.1.600.12.1.5', 'powerSupplyStatus', 'powerSupplyLocationName', 'MIB-Dell-10892', $status],
            ['intrusionTable', '.1.3.6.1.4.1.674.10892.1.300.70.1.5', 'intrusionStatus', '', 'MIB-Dell-10892', $status],
            ['controllerTable', '.1.3.6.1.4.1.674.10893.1.20.130.1.1.5', 'controllerState', 'controllerName', 'StorageManagement-MIB', [
                ['value' => 1, 'generic' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 1, 'descr' => 'offline'],
                ['value' => 6, 'generic' => 2, 'descr' => 'degraded'],
            ]],
            ['arrayDiskTable', '.1.3.6.1.4.1.674.10893.1.20.130.4.1.4', 'arrayDiskState', 'arrayDiskName', 'StorageManagement-MIB', [
                ['value' => 1, 'generic' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 2, 'descr' => 'offline'],
                ['value' => 5, 'generic' => 2, 'descr' => 'degraded'],
                ['value' => 6, 'generic' => 1, 'descr' => 'recovering'],
                ['value' => 7, 'generic' => 1, 'descr' => 'removed'],
                ['value' => 8, 'generic' => 3, 'descr' => 'non-raid'],
                ['value' => 9, 'generic' => 1, 'descr' => 'notReady'],
                ['value' => 10, 'generic' => 1, 'descr' => 'resynching'],
                ['value' => 11, 'generic' => 1, 'descr' => 'replacing'],
                ['value' => 12, 'generic' => 1, 'descr' => 'spinningDown'],
                ['value' => 13, 'generic' => 1, 'descr' => 'rebuild'],
                ['value' => 14, 'generic' => 1, 'descr' => 'noMedia'],
                ['value' => 15, 'generic' => 1, 'descr' => 'formatting'],
                ['value' => 16, 'generic' => 1, 'descr' => 'diagnostics'],
                ['value' => 17, 'generic' => 2, 'descr' => 'predictiveFailure'],
                ['value' => 18, 'generic' => 1, 'descr' => 'initializing'],
                ['value' => 19, 'generic' => 1, 'descr' => 'foreign'],
                ['value' => 20, 'generic' => 1, 'descr' => 'clear'],
                ['value' => 21, 'generic' => 2, 'descr' => 'unsupported'],
                ['value' => 22, 'generic' => 2, 'descr' => 'incompatible'],
                ['value' => 23, 'generic' => 2, 'descr' => 'readOnly'],
            ]],
            ['virtualDiskTable', '.1.3.6.1.4.1.674.10893.1.20.140.1.1.4', 'virtualDiskState', 'virtualDiskDeviceName', 'StorageManagement-MIB', [
                ['value' => 0, 'generic' => 3, 'descr' => 'unknown'],
                ['value' => 1, 'generic' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'descr' => 'failed'],
                ['value' => 3, 'generic' => 1, 'descr' => 'online'],
                ['value' => 4, 'generic' => 2, 'descr' => 'offline'],
                ['value' => 6, 'generic' => 2, 'descr' => 'degraded'],
                ['value' => 7, 'generic' => 1, 'descr' => 'verifying'],
                ['value' => 15, 'generic' => 1, 'descr' => 'resynching'],
                ['value' => 16, 'generic' => 1, 'descr' => 'regenerating'],
                ['value' => 18, 'generic' => 2, 'descr' => 'failedRedundancy'],
                ['value' => 24, 'generic' => 1, 'descr' => 'rebuilding'],
                ['value' => 26, 'generic' => 1, 'descr' => 'formatting'],
                ['value' => 32, 'generic' => 1, 'descr' => 'reconstructing'],
                ['value' => 35, 'generic' => 1, 'descr' => 'initializing'],
                ['value' => 36, 'generic' => 1, 'descr' => 'backgroundInit'],
                ['value' => 52, 'generic' => 2, 'descr' => 'permanentlyDegraded'],
            ]],
            ['batteryTable', '.1.3.6.1.4.1.674.10893.1.20.130.15.1.4', 'batteryState', 'batteryName', 'StorageManagement-MIB', [
                ['value' => 1, 'generic' => 0, 'descr' => 'ready'],
                ['value' => 2, 'generic' => 2, 'descr' => 'failed'],
                ['value' => 6, 'generic' => 2, 'descr' => 'degraded'],
                ['value' => 7, 'generic' => 1, 'descr' => 'reconditioning'],
                ['value' => 9, 'generic' => 1, 'descr' => 'high'],
                ['value' => 10, 'generic' => 1, 'descr' => 'low'],
                ['value' => 12, 'generic' => 1, 'descr' => 'charging'],
                ['value' => 21, 'generic' => 2, 'descr' => 'missing'],
                ['value' => 36, 'generic' => 1, 'descr' => 'learning'],
            ]],
        ];
    }
}
