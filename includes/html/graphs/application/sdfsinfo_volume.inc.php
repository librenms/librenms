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

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Volume';
$unitlen = 6;
$bigdescrlen = 25;
$smalldescrlen = 25;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;
$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);
$array = [
    'files' => ['descr' => 'Files', 'colour' => '000000'],
    'vol_capacity' => ['descr' => 'Capacity (GB)', 'colour' => '2A7A12'],
    'vol_logic_size' => ['descr' => 'Logic capacity (GB)', 'colour' => '74127A'],
    'vol_max_load' => ['descr' => 'Max load (%)', 'colour' => 'F44842'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
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

require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
