<?php
/*
 * Schleifenbauer.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use SnmpQuery;

class Schleifenbauer extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        $master_unit = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.31034.12.1.1.1.2.4.1.2.1', '-Oqv');

        $oids = [
            'hardware' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.5.$master_unit",
            'serial' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.6.$master_unit",
            'firmware' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.2.$master_unit",
            'build' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.3.$master_unit",
        ];

        $data = snmp_get_multi_oid($this->getDeviceArray(), $oids);

        $device->hardware = $data[$oids['hardware']] ?? null;
        $device->serial = $data[$oids['serial']] ?? null;
        $device->version = $data[$oids['firmware']] ?? null;
        if (! empty($data[$oids['build']])) {
            $device->version = trim("$device->version ({$data[$oids['build']]})");
        }
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $sdbMgmtStsDevices = SnmpQuery::get('SCHLEIFENBAUER-DATABUS-MIB::sdbMgmtStsDevices.0')->value();

        // Only spawn databus ring entities when the device is not stand-alone.
        if ($sdbMgmtStsDevices > 1) {
            $entPhysicalContainedIn = 1;
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => "Schleifenbauer databus ring ($sdbMgmtStsDevices units)",
                'entPhysicalClass' => 'stack',
                'entPhysicalName' => 'Schleifenbauer Databus',
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                'entPhysicalIsFRU' => 'false',
            ]));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => 'Databus Ring State Sensor (0 = open, 1 = closed)',
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => 'State Sensor',
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                'entPhysicalIsFRU' => 'false',
            ]));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => 'Duplicate Device Address Sensor (#)',
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => 'State Sensor',
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                'entPhysicalIsFRU' => 'false',
            ]));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => 'New Device Detection Sensor (#)',
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => 'State Sensor',
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                'entPhysicalIsFRU' => 'false',
            ]));
        } else {
            $entPhysicalContainedIn = 0;
        }

        // Let's gather some data about the units..
        $sdbDevCfMaximumLoad = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfMaximumLoad')->table(1);
        $sdbDevCfOutletsTotal = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfOutletsTotal')->table(1);
        $sdbDevCfSensors = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfSensors')->table(1);

        // In a large databus ring, snmpwalking this OID may crash the discovery half way through.
        // So, we only discover and enumerate outlets if this is a stand-alone, non-databus unit.
        $sdbOutputs = $sdbMgmtStsDevices == 1 ? SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevOutName')->table(2) : [];

        // Let's gather some data about the databus ring..
        $sdbDev = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdTable')->table(1);

        // And let's gather some data about the inputs, outputs, and sensors on those units..
        $sdbDevInputs = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevInName')->table(2);
        $sdbSensors = SnmpQuery::walk('SCHLEIFENBAUER-DATABUS-MIB::sdbDevSnsTable')->table(2);

        foreach ($sdbDevInputs as $sdbDevIdIndex => $sdbDevInNames) {
            $data = $sdbDev[$sdbDevIdIndex];
            $unitEntPhysicalIndex = $sdbDevIdIndex * 10;

            // We are determining the $entPhysicalAlias for this PDU based on a few optional user-customizable fields.
            $entPhysicalAlias = $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdName'] ?: null;
            if ($entPhysicalAlias && $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdLocation']) {
                $entPhysicalAlias .= ' @ ' . $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdLocation'];
            }

            $outletsTotal = $sdbDevCfOutletsTotal[$sdbDevIdIndex]['SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfOutletsTotal'];
            $phasesTotal = count($sdbDevInNames);
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $unitEntPhysicalIndex,
                'entPhysicalDescr' => "Schleifenbauer $phasesTotal-phase, $outletsTotal-outlet PDU",
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => 'Schleifenbauer PDU - SPDM v' . $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdFirmwareVersion'],
                'entPhysicalModelName' => $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdProductId'],
                'entPhysicalSerialNum' => $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdSerialNumber'],
                'entPhysicalContainedIn' => $entPhysicalContainedIn,
                'entPhysicalParentRelPos' => $sdbDevIdIndex,
                'entPhysicalHardwareRev' => 'SO# ' . $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdSalesOrderNumber'],
                'entPhysicalSoftwareRev' => $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdFirmwareVersion'],
                'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                'entPhysicalAlias' => $entPhysicalAlias,
                'entPhysicalIsFRU' => 'true',
                'entPhysicalAssetID' => $data['SCHLEIFENBAUER-DATABUS-MIB::sdbDevIdVanityTag'],
            ]));

            // Since a fully numerical entPhysicalIndex is only available for the actual PDU, we are calculating a fake entPhysicalIndex to avoid namespace collision. We have an Integer32 of space per IETF RFC6933 anyway.
            // The maximum sdbMgmtCtrlDevUnitAddress is 255, but multiplying by 1 million for namespace size. Add +100k for every top-level index below a PDU.
            foreach ($sdbDevInNames as $sdbDevInIndex => $sdbDevInName) {
                $inputIndex = $sdbDevIdIndex * 1000000 + 100000 + $sdbDevInIndex * 1000; // +100k for the first top-level namespace. Add 1000 * sdbDevInIndex which goes up to 48. Leave 3 variable digits at the end.
                $entPhysicalDescr = $sdbDevCfMaximumLoad[$sdbDevIdIndex]['SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfMaximumLoad'] . 'A input phase';
                $entPhysicalName = 'Input L' . $sdbDevInIndex;

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $inputIndex,
                    'entPhysicalDescr' => $entPhysicalDescr,
                    'entPhysicalClass' => 'powerSupply',
                    'entPhysicalName' => $entPhysicalName,
                    'entPhysicalContainedIn' => $unitEntPhysicalIndex,
                    'entPhysicalParentRelPos' => $sdbDevInIndex,
                    'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                    'entPhysicalAlias' => $sdbDevInName['SCHLEIFENBAUER-DATABUS-MIB::sdbDevInName'],
                    'entPhysicalIsFRU' => 'false',
                ]));

                // Enumerate sensors under the Input
                $this->enumerateSensors($inventory, $inputIndex, $entPhysicalName);
            }

            // Only enumerate outlets if this is a stand-alone, non-databus unit.
            if ($sdbMgmtStsDevices == 1) {
                // Check if we can find any outlets on this PDU..
                if (isset($sdbOutputs[$sdbDevIdIndex])) {
                    // We found outlets, so let's spawn an Outlet Backplane.
                    $outletBackplaneIndex = $sdbDevIdIndex * 1000000 + 200000; // +200k for the second top-level index namespace.
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $outletBackplaneIndex,
                        'entPhysicalDescr' => $outletsTotal . ' outlets',
                        'entPhysicalClass' => 'backplane',
                        'entPhysicalName' => 'Outlets',
                        'entPhysicalContainedIn' => $unitEntPhysicalIndex,
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                        'entPhysicalIsFRU' => 'false',
                    ]));

                    foreach ($sdbOutputs[$sdbDevIdIndex] as $sdbDevOutIndex => $output) {
                        $outletIndex = $outletBackplaneIndex + $sdbDevOutIndex * 1000; // +200k for the second top-level index namespace. Add 1000 * sdbDevOutIndex which goes up to 48. Leave 3 variable digits at the end.
                        $entPhysicalName = 'Outlet #' . $sdbDevOutIndex;

                        $inventory->push(new EntPhysical([
                            'entPhysicalIndex' => $outletIndex,
                            'entPhysicalDescr' => 'PDU outlet',
                            'entPhysicalClass' => 'powerSupply',
                            'entPhysicalName' => $entPhysicalName,
                            'entPhysicalContainedIn' => $outletBackplaneIndex,
                            'entPhysicalParentRelPos' => $sdbDevOutIndex,
                            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                            'entPhysicalAlias' => $output['SCHLEIFENBAUER-DATABUS-MIB::sdbDevOutName'],
                            'entPhysicalIsFRU' => 'false',
                        ]));

                        // Enumerate sensors under the Outlet
                        $this->enumerateSensors($inventory, $outletIndex, $entPhysicalName);
                    }
                }
            }

            // Check if we can find any external sensor connections on this PDU..
            if (isset($sdbSensors[$sdbDevIdIndex])) {
                // We found at least one sensor connection, so let's spawn a Sensor Container.
                $sensorContainerIndex = $sdbDevIdIndex * 1000000 + 300000; // +300k for the third top-level index namespace.
                $entPhysicalDescr = $sdbDevCfSensors[$sdbDevIdIndex]['SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfSensors'] == 1 ? '1 external sensor' : $sdbDevCfSensors[$sdbDevIdIndex]['SCHLEIFENBAUER-DATABUS-MIB::sdbDevCfSensors'] . ' external sensors';

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $sensorContainerIndex,
                    'entPhysicalDescr' => $entPhysicalDescr,
                    'entPhysicalClass' => 'container',
                    'entPhysicalName' => 'Sensor Container',
                    'entPhysicalContainedIn' => $unitEntPhysicalIndex,
                    'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                    'entPhysicalIsFRU' => 'false',
                ]));

                foreach ($sdbSensors[$sdbDevIdIndex] as $sdbDevSnsIndex => $sensor) {
                    $sensorIndex = $sensorContainerIndex + $sdbDevSnsIndex * 1000; // +300k for the third top-level index namespace. Add 1000 * sdbDevSnsIndex which goes up to 16. Leave 3 variable digits at the end.
                    $entPhysicalName = 'External Sensor #' . $sdbDevSnsIndex;
                    $entPhysicalDescr = match ($sensor['SCHLEIFENBAUER-DATABUS-MIB::sdbDevSnsType']) {
                        'T' => 'Temperature sensor (°C)',
                        'H' => 'Humidity sensor (%)',
                        'I' => 'Dry switch contact (binary)',
                        default => null,
                    };

                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $sensorIndex,
                        'entPhysicalDescr' => $entPhysicalDescr,
                        'entPhysicalClass' => 'sensor',
                        'entPhysicalName' => $entPhysicalName,
                        'entPhysicalContainedIn' => $sensorContainerIndex,
                        'entPhysicalParentRelPos' => $sdbDevSnsIndex,
                        'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
                        'entPhysicalAlias' => $sensor['SCHLEIFENBAUER-DATABUS-MIB::sdbDevSnsName'],
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }
        }

        return $inventory;
    }

    private function enumerateSensors(Collection $inventory, int $inputIndex, string $entPhysicalName): void
    {
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => $inputIndex + 110,
            'entPhysicalDescr' => $entPhysicalName . ' voltage sensor (V)',
            'entPhysicalClass' => 'sensor',
            'entPhysicalName' => 'Voltage Sensor',
            'entPhysicalContainedIn' => $inputIndex,
            'entPhysicalParentRelPos' => 1,
            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
            'entPhysicalIsFRU' => 'false',
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => $inputIndex + 120,
            'entPhysicalDescr' => $entPhysicalName . ' RMS current sensor (A)',
            'entPhysicalClass' => 'sensor',
            'entPhysicalName' => 'Current Sensor',
            'entPhysicalContainedIn' => $inputIndex,
            'entPhysicalParentRelPos' => 2,
            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
            'entPhysicalIsFRU' => 'false',
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => $inputIndex + 130,
            'entPhysicalDescr' => $entPhysicalName . ' apparent power sensor (W)',
            'entPhysicalClass' => 'sensor',
            'entPhysicalName' => 'Power Sensor',
            'entPhysicalContainedIn' => $inputIndex,
            'entPhysicalParentRelPos' => 3,
            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
            'entPhysicalIsFRU' => 'false',
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => $inputIndex + 140,
            'entPhysicalDescr' => $entPhysicalName . ' lifetime power consumed sensor (kWh)',
            'entPhysicalClass' => 'sensor',
            'entPhysicalName' => 'Power Consumed Sensor',
            'entPhysicalContainedIn' => $inputIndex,
            'entPhysicalParentRelPos' => 4,
            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
            'entPhysicalIsFRU' => 'false',
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => $inputIndex + 150,
            'entPhysicalDescr' => $entPhysicalName . ' power factor sensor (ratio)',
            'entPhysicalClass' => 'sensor',
            'entPhysicalName' => 'Power Factor Sensor',
            'entPhysicalContainedIn' => $inputIndex,
            'entPhysicalParentRelPos' => 5,
            'entPhysicalMfgName' => 'Schleifenbauer Products B.V.',
            'entPhysicalIsFRU' => 'false',
        ]));
    }
}
