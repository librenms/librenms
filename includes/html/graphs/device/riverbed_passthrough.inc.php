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
*
*/

require 'includes/html/graphs/common.inc.php';
$rrd_filename = Rrd::name($device['hostname'], 'riverbed_passthrough');

$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Bandwidth Passthrough';
$unitlen = 21;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 80;
$data_sources = [
    'bw_in' => ['descr' => 'In', 'colour' => '30649b'],
    'bw_out' => ['descr' => 'Out', 'colour' => '9b3030'],
    'bw_total' => ['descr' => 'Total', 'colour' => '000000'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($data_sources as $ds => $var) {
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
