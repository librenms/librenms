<?php
/**
 * unix.inc.php
 *
 * LibreNMS state sensor discovery module for UNIX based OS
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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
use LibreNMS\Util\Oid;

$snmpData = SnmpQuery::cache()->hideMib()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut"')->table(3);
if (! empty($snmpData)) {
    echo 'UPS-NUT-MIB: ' . PHP_EOL;
    $snmpData = array_shift($snmpData); // drop [ups-nut]
    $sensors = [
        ['state_name' => 'UPSOnLine', 'genericT' => 0, 'genericF' => 1, 'descr' => 'UPS on line'],
        ['state_name' => 'UPSOnBattery', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS on battery'],
        ['state_name' => 'UPSLowBattery', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS low battery'],
        ['state_name' => 'UPSHighBattery', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS high battery'],
        ['state_name' => 'UPSBatteryReplace', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery needs to be replaced'],
        ['state_name' => 'UPSBatteryCharging', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery is charging'],
        ['state_name' => 'UPSBatteryDischarging', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS the battery is discharging'],
        ['state_name' => 'UPSUPSBypass', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS bypass circuit is active'],
        ['state_name' => 'UPSRuntimeCalibration', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is currently performing runtime calibration'],
        ['state_name' => 'UPSOffline', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS is offline and is not supplying power to the load'],
        ['state_name' => 'UPSUPSOverloaded', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS is overloaded.'],
        ['state_name' => 'UPSUPSBuck', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is trimming incoming voltage'],
        ['state_name' => 'UPSUPSBoost', 'genericT' => 1, 'genericF' => 0, 'descr' => 'UPS is boosting incoming voltage'],
        ['state_name' => 'UPSForcedShutdown', 'genericT' => 2, 'genericF' => 0, 'descr' => 'UPS forced shutdown'],
    ];
    foreach ($sensors as $index => $sensor) {
        $sensor_oid = 9 + $index;

        if (! empty($snmpData[$sensor_oid])) {
            $oid = Oid::toNumeric('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut".' . $sensor_oid);
            $value = current($snmpData[$sensor_oid]);
            $state_name = $sensor['state_name'];
            $descr = $sensor['descr'];
            $states = [
                ['value' => 0, 'generic' => $sensor['genericF'], 'graph' => 1, 'descr' => 'False'],
                ['value' => 1, 'generic' => $sensor['genericT'], 'graph' => 1, 'descr' => 'True'],
            ];

            create_state_index($state_name, $states);

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $oid, $sensor_oid, $state_name, $descr, '1', '1', null, null, null, null, $value, 'snmp', null, null, null, 'ups-nut');

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $sensor_oid);
        }
    }
}
