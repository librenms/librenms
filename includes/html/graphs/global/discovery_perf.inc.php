<?php

/**
 * discovery_perf.php
 *
 * Global discovery performance graph - shows total discovery time across all devices
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
 * @copyright  2024 LibreNMS Contributors
 */

use App\Models\Device;

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$cdef = [];
$suffix = '';
$cdefX = [];
$suffixX = '';

foreach (Device::pluck('hostname') as $index => $hostname) {
    $rrd_filename = Rrd::name($hostname, 'discovery-perf');
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options[] = "DEF:discoveryRaw$index=$rrd_filename:discovery:AVERAGE";
        // change undefined to 0
        $rrd_options[] = "CDEF:discovery$index=discoveryRaw$index,UN,0,discoveryRaw$index,IF";
        $rrd_options[] = "CDEF:datapresent$index=discoveryRaw$index,UN,0,1,IF";
        $cdef[] = 'discovery' . $index . $suffix;
        $suffix = ',+';
        if ($graph_params->visible('previous')) {
            $rrd_options[] = "DEF:discoveryRawX$index=$rrd_filename:discovery:AVERAGE:start=$prev_from:end=$from";
            // change undefined to 0
            $rrd_options[] = "CDEF:discoveryX$index=discoveryRawX$index,UN,0,discoveryRawX$index,IF";
            $rrd_options[] = "SHIFT:discoveryX$index:$period";
            $rrd_options[] = "CDEF:datapresentX$index=discoveryRawX$index,UN,0,1,IF";
            $rrd_options[] = "SHIFT:datapresentX$index:$period";
            $cdefX[] = 'discoveryX' . $index . $suffixX;
            $suffixX = ',+';
        }
    }
}

$total_color = \App\Facades\LibrenmsConfig::get('graph_colours.mixed.2', '36393D');
$device_color = \App\Facades\LibrenmsConfig::get('graph_colours.mixed.6', 'CCCCCC');

// sum all the discovery times
$discovery_cdef = implode(',', $cdef);
$rrd_options[] = 'CDEF:discovery=' . $discovery_cdef;
// sum the data present value to get a count
$rrd_options[] = 'CDEF:datacount=' . str_replace('discovery', 'datapresent', $discovery_cdef);
// if no data for interval, push unknowns
$rrd_options[] = 'CDEF:discoveryundef=datacount,0,EQ,UNKN,discovery,IF';
$rrd_options[] = 'CDEF:datacountundef=datacount,0,EQ,UNKN,datacount,IF';
// divide by count of datas to get accurate average
$rrd_options[] = 'CDEF:avgdisc=discovery,datacountundef,/';
// legend
$rrd_options[] = 'COMMENT:Seconds             Cur       Min        Max       Avg\\n';
$rrd_options[] = 'LINE1.25:discoveryundef#' . $total_color . ':Total      ';
$rrd_options[] = 'AREA:discoveryundef#' . $total_color . '70';
$rrd_options[] = 'GPRINT:discoveryundef:LAST:%8.2lf';
$rrd_options[] = 'GPRINT:discoveryundef:MIN:%8.2lf';
$rrd_options[] = 'GPRINT:discoveryundef:MAX:%8.2lf';
$rrd_options[] = 'GPRINT:discoveryundef:AVERAGE:%8.2lf\\n';
$rrd_options[] = 'LINE1.25:avgdisc#' . $device_color . ':Device Avg ';
$rrd_options[] = 'AREA:avgdisc#' . $device_color . '70';
$rrd_options[] = 'GPRINT:avgdisc:LAST:%8.2lf';
$rrd_options[] = 'GPRINT:avgdisc:MIN:%8.2lf';
$rrd_options[] = 'GPRINT:avgdisc:MAX:%8.2lf';
$rrd_options[] = 'GPRINT:avgdisc:AVERAGE:%8.2lf\\n';
if ($graph_params->visible('previous')) {
    $rrd_options[] = 'COMMENT:\\n';
    $discoveryX_cdef = implode(',', $cdefX);
    $rrd_options[] = 'CDEF:discoveryX=' . $discoveryX_cdef;
    $rrd_options[] = 'CDEF:datacountX=' . str_replace('discoveryX', 'datapresentX', $discoveryX_cdef);
    $rrd_options[] = 'CDEF:discoveryundefX=datacountX,0,EQ,UNKN,discoveryX,IF';
    $rrd_options[] = 'LINE1.25:discoveryundefX#46494D:Prev Discovery';
    $rrd_options[] = 'GPRINT:discoveryundefX:LAST:%8.2lf';
    $rrd_options[] = 'GPRINT:discoveryundefX:MIN:%8.2lf';
    $rrd_options[] = 'GPRINT:discoveryundefX:MAX:%8.2lf';
    $rrd_options[] = 'GPRINT:discoveryundefX:AVERAGE:%8.2lf\\n';
}
