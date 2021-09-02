<?php
/*
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
* @copyright  2016 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*
*/

use LibreNMS\RRD\RrdDefinition;

// (2016-11-25, R.Morris) ups-nut, try "extend" -> if not, fall back to "exec" support.
// -> Similar to approach used by Distro, but skip "legacy UCD-MIB shell support"
//
//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ups-nut"
$name = 'ups-nut';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.117.112.115.45.110.117.116';
$ups_nut = snmp_get($device, $oid, '-Oqv');

// If "extend" (used above) fails, try "exec" support.
// Note, exec always splits outputs on newline, so need to use snmp_walk (not a single SNMP entry!)
if (! $ups_nut) {
    // Data is in an array, due to how "exec" works with ups-nut.sh output, so snmp_walk to retrieve it
    $oid = '.1.3.6.1.4.1.2021.7890.2.101';
    $ups_nut = snmp_walk($device, $oid, '-Oqv');
}
//print_r(array_values(explode("\n", $ups_nut)));

echo ' ' . $name;

// (2020-05-13, Jon.W) Added ups status data and updated ups-nut.sh script.
[
    $charge,
    $battery_low,
    $remaining,
    $bat_volt,
    $bat_nom,
    $line_nom,
    $input_volt,
    $load,
    $UPSOnLine,
    $UPSOnBattery,
    $UPSLowBattery,
    $UPSHighBattery,
    $UPSBatteryReplace,
    $UPSBatteryCharging,
    $UPSBatteryDischarging,
    $UPSUPSBypass,
    $UPSRuntimeCalibration,
    $UPSOffline,
    $UPSUPSOverloaded,
    $UPSUPSBuck,
    $UPSUPSBoost,
    $UPSForcedShutdown
    ] = explode("\n", $ups_nut);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('charge', 'GAUGE', 0, 100)
    ->addDataset('battery_low', 'GAUGE', 0, 100)
    ->addDataset('time_remaining', 'GAUGE', 0)
    ->addDataset('battery_voltage', 'GAUGE', 0)
    ->addDataset('battery_nominal', 'GAUGE', 0)
    ->addDataset('line_nominal', 'GAUGE', 0)
    ->addDataset('input_voltage', 'GAUGE', 0)
    ->addDataset('load', 'GAUGE', 0, 100);

$fields = [
    'charge' => $charge,
    'battery_low' => $battery_low,
    'time_remaining' => $remaining / 60,
    'battery_voltage' => $bat_volt,
    'battery_nominal' => $bat_nom,
    'line_nominal' => $line_nom,
    'input_voltage' => $input_volt,
    'load' => $load,
];

$sensors = [
    ['state_name' => 'UPSOnLine', 'value' => $UPSOnLine],
    ['state_name' => 'UPSOnBattery', 'value' => $UPSOnBattery],
    ['state_name' => 'UPSLowBattery', 'value' => $UPSLowBattery],
    ['state_name' => 'UPSHighBattery', 'value' => $UPSHighBattery],
    ['state_name' => 'UPSBatteryReplace', 'value' => $UPSBatteryReplace],
    ['state_name' => 'UPSBatteryCharging', 'value' => $UPSBatteryCharging],
    ['state_name' => 'UPSBatteryDischarging', 'value' => $UPSBatteryDischarging],
    ['state_name' => 'UPSUPSBypass', 'value' => $UPSUPSBypass],
    ['state_name' => 'UPSRuntimeCalibration', 'value' => $UPSRuntimeCalibration],
    ['state_name' => 'UPSOffline', 'value' => $UPSOffline],
    ['state_name' => 'UPSUPSOverloaded', 'value' => $UPSUPSOverloaded],
    ['state_name' => 'UPSUPSBuck', 'value' => $UPSUPSBuck],
    ['state_name' => 'UPSUPSBoost', 'value' => $UPSUPSBoost],
    ['state_name' => 'UPSForcedShutdown', 'value' => $UPSForcedShutdown],
];

foreach ($sensors as $index => $sensor) {
    $rrd_def->addDataset($sensor['state_name'], 'GAUGE', 0);
    $fields[$sensor['state_name']] = $sensor['value'];
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $ups_nut, $fields);
