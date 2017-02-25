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
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       http://librenms.org
* @copyright  2017 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ups-apcups"
$name = 'ups-apcups';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.117.112.115.45.97.112.99.117.112.115';
$ups_apcups = trim(snmp_get($device, $oid, '-Oqv'), '"');

echo ' '.$name;

list ($line_volt, $load, $charge, $remaining, $bat_volt, $line_nominal, $bat_nominal) = explode("\n", $ups_apcups);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('charge', 'GAUGE', 0, 100)
    ->addDataset('time_remaining', 'GAUGE', 0)
    ->addDataset('battery_nominal', 'GAUGE', 0)
    ->addDataset('battery_voltage', 'GAUGE', 0)
    ->addDataset('input_voltage', 'GAUGE', 0)
    ->addDataset('nominal_voltage', 'GAUGE', 0)
    ->addDataset('load', 'GAUGE', 0, 100);

$fields = array(
    'charge' => $charge,
    'time_remaining' => $remaining,
    'battery_nominal' => $bat_nominal,
    'battery_voltage' => $bat_volt,
    'input_voltage' => $line_volt,
    'nominal_voltage' => $line_nominal,
    'load' => $load
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
