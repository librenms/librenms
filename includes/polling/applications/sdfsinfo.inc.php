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

$name = 'sdfsinfo';
$app_id = $app['app_id'];
$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.115.100.102.115.105.110.102.111';

d_echo($name);

$sdfsinfo = snmp_walk($device, $oid, $options);

if (is_string($sdfsinfo)) {
    $rrd_name = ['app', $name, $app_id];

    $rrd_def = RrdDefinition::make()
        ->addDataset('files', 'GAUGE', 0)
        ->addDataset('vol_capacity', 'GAUGE', 0)
        ->addDataset('vol_logic_size', 'GAUGE', 0)
        ->addDataset('vol_max_load', 'GAUGE', 0)
        ->addDataset('dup_data', 'GAUGE', 0)
        ->addDataset('blocks_unique', 'GAUGE', 0)
        ->addDataset('blocks_compressed', 'GAUGE', 0)
        ->addDataset('cluster_copies', 'GAUGE', 0)
        ->addDataset('dedup_rate', 'GAUGE', 0)
        ->addDataset('actual_savings', 'GAUGE', 0)
        ->addDataset('comp_rate', 'GAUGE', 0);

    [$files, $vol_capacity, $vol_logic_size, $vol_max_load, $dup_data, $blocks_unique, $blocks_compressed, $cluster_copies, $dedup_rate, $actual_savings, $comp_rate] = explode(' ', $sdfsinfo);

    $fields = [
        'files' => $files,
        'vol_capacity' => $vol_capacity,
        'vol_logic_size' => $vol_logic_size,
        'vol_max_load' => $vol_max_load,
        'dup_data' => $dup_data,
        'blocks_unique' => $blocks_unique,
        'blocks_compressed' => $blocks_compressed,
        'cluster_copies' => $cluster_copies,
        'dedup_rate' => $dedup_rate,
        'actual_savings' => $actual_savings,
        'comp_rate' => $comp_rate,
    ];

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    update_application($app, $sdfsinfo, $fields);

    unset($sdfsinfo, $rrd_name, $rrd_def, $data, $fields, $tags);
}
