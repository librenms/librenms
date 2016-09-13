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
* @copyright  2016 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ups-nut"
$name = 'ups-nut';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.117.112.115.45.110.117.116';
$ups_nut = snmp_get($device, $oid, '-Oqv');

echo ' '.$name;

list ($charge, $bat_low, $remaining, $bat_volt, $model, $serial, $input_vol, $line_vol, $load) = explode("\n", $ups_nut);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:charge:GAUGE:600:0:100',
    'DS:battery_low:GAUGE:600:0:100',
    'DS:time_remaining:GAUGE:600:0:U',
    'DS:battery_voltage:GAUGE:600:0:U',
    'DS:input_voltage:GAUGE:600:0:U',
    'DS:nominal_voltage:GAUGE:600:0:U',
    'DS:load:GAUGE:600:0:100'
);

$fields = array(
    'charge' => $charge,
    'battery_low' => $bat_low,
    'time_remaining' => $remaining,
    'battery_voltage' => $bat_volt,
    'input_voltage' => $input_vol,
    'nominal_voltage' => $line_vol,
    'load' => $load,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
