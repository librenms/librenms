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

global $config;

//NET-SNMP-EXTEND-MIB::nsExtendOutLine.\"ups-nut\"
$ups_model = snmp_get($device, '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.4', '-Oqv');
$ups_serial = snmp_get($device, '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.5', '-Oqv');
$ups_details = $ups_model.' (SN:'.$ups_serial.')';

$graphs = array(
    'ups-nut_remaining' => 'Remaining time: '.$ups_details,
    'ups-nut_load' => 'Load: '.$ups_details,
    'ups-nut_voltage_battery' => 'Battery voltage: '.$ups_details,
    'ups-nut_charge' => 'Charge: '.$ups_details,
    'ups-nut_voltage_input' => 'Input voltage: '.$ups_details,
);

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
