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
* @copyright  2020 LibreNMS
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$unit_text = 'InnoDB IO Pending';
$unitlen = 17;
$bigdescrlen = 25;
$smalldescrlen = 25;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;
$rrd_filename = rrd_name($device['hostname'], ['app', $app['app_type'], $app['app_id']]);

$array = [
    'ib_iop_log' => ['descr' => 'AIO Log', 'colour' => '4ca3dd',],
    'ib_iop_sync' => ['descr' => 'AIO Sync', 'colour' => 'ffa500',],
    'ib_iop_aioread' => ['descr' => 'AIO read', 'colour' => '5ac18e',],
    'ib_iop_aiowrite' => ['descr' => 'AIO write', 'colour' => 'f6546a',],
    'ib_iop_ibuf_aio' => ['descr' => 'AIO insert buf', 'colour' => '065535',],
    'ib_iop_flush_log' => ['descr' => 'Flush log', 'colour' => 'ff6666',],
    'ib_iop_flush_bpool' => ['descr' => 'Flush bpool', 'colour' => '800000',],
];

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
