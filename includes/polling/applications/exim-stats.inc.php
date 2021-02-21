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
* @copyright  2017 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."exim-stats"
$name = 'exim-stats';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.101.120.105.109.45.115.116.97.116.115';
$stats = snmp_get($device, $oid, '-Oqv');

echo ' ' . $name;

[$frozen, $queue] = explode("\n", $stats);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('frozen', 'GAUGE', 0)
    ->addDataset('queue', 'GAUGE', 0);

$fields = [
    'frozen' => intval(trim($frozen, '"')),
    'queue' => intval(trim($queue, '"')),
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $stats, $fields);
