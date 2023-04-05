<?php
/**
 * poller_perf.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Models\Device;

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$cdef = [];
$avg_cdef = [];
$suffix = '';
$cdefX = [];
$suffixX = '';

foreach (Device::pluck('hostname') as $index => $hostname) {
    $rrd_filename = Rrd::name($hostname, 'poller-perf');
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= " DEF:pollerRaw$index=$rrd_filename:poller:AVERAGE";
        // change undefined to 0
        $rrd_options .= " CDEF:poller$index=pollerRaw$index,UN,0,pollerRaw$index,IF";
        $rrd_options .= " CDEF:datapresent$index=pollerRaw$index,UN,0,1,IF";
        $cdef[] = 'poller' . $index . $suffix;
        $suffix = ',+';
        if ($graph_params->visible('previous')) {
            $rrd_options .= " DEF:pollerRawX$index=$rrd_filename:poller:AVERAGE:start=$prev_from:end=$from";
            // change undefined to 0
            $rrd_options .= " CDEF:pollerX$index=pollerRawX$index,UN,0,pollerRawX$index,IF";
            $rrd_options .= " SHIFT:pollerX$index:$period";
            $cdefX[] = 'pollerX' . $index . $suffixX;
            $suffixX = ',+';
        }
    }
}

$total_color = \LibreNMS\Config::get('graph_colours.mixed.2', '36393D');
$device_color = \LibreNMS\Config::get('graph_colours.mixed.6', '36393D');

// sum all the poll times
$rrd_options .= ' CDEF:poller=' . implode(',', $cdef);
// sum the data present value to get a count
$rrd_options .= ' CDEF:datacount=' . str_replace('poller', 'datapresent', implode(',', $cdef));
// if no data for interval, push unknowns
$rrd_options .= ' CDEF:pollerundef=datacount,0,EQ,UNKN,poller,IF';
$rrd_options .= ' CDEF:datacountundef=datacount,0,EQ,UNKN,datacount,IF';
// divide by count of datas to get accurate average
$rrd_options .= ' CDEF:avgpol=poller,datacountundef,/';
// legend
$rrd_options .= " 'COMMENT:Seconds            Cur       Min        Max       Avg\\n'";
$rrd_options .= ' LINE1.25:pollerundef#' . $total_color . ":'Total     '";
$rrd_options .= ' AREA:pollerundef#' . $total_color . "70";
$rrd_options .= ' GPRINT:pollerundef:LAST:%8.2lf  GPRINT:poller:MIN:%8.2lf';
$rrd_options .= ' GPRINT:pollerundef:MAX:%8.2lf  GPRINT:poller:AVERAGE:%8.2lf\\n';
$rrd_options .= ' LINE1.25:avgpol#' . $device_color . ":'Per Device'";
$rrd_options .= ' AREA:avgpol#' . $device_color . '70';
$rrd_options .= ' GPRINT:avgpol:LAST:%8.2lf  GPRINT:avgpol:MIN:%8.2lf';
$rrd_options .= ' GPRINT:avgpol:MAX:%8.2lf  GPRINT:avgpol:AVERAGE:%8.2lf\\n';
if ($graph_params->visible('previous')) {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= ' CDEF:pollerX=' . implode(',', $cdefX);
    $rrd_options .= ' CDEF:avgpolX=pollerX,' . count($cdefX) . ',/';
    $rrd_options .= " LINE1.25:pollerX#CCCCCC:'Prev Poller'\t";
    $rrd_options .= ' GPRINT:pollerX:MIN:%8.2lf';
    $rrd_options .= ' GPRINT:pollerX:MAX:%8.2lf  GPRINT:pollerX:AVERAGE:%8.2lf';
    $rrd_options .= " 'GPRINT:avgpolX:AVERAGE:%8.2lf\\n'";
}
