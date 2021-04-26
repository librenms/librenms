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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Models\Device;

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$cdef = [];
$suffix = '';
$cdefX = [];
$suffixX = '';

foreach (Device::pluck('hostname') as $index => $hostname) {
    $rrd_filename = Rrd::name($hostname, 'poller-perf');
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= " DEF:pollerRaw$index=$rrd_filename:poller:AVERAGE";
        // change undefined to 0
        $rrd_options .= " CDEF:poller$index=pollerRaw$index,UN,0,pollerRaw$index,IF";
        $cdef[] = 'poller' . $index . $suffix;
        $suffix = ',+';
        if ($_GET['previous'] == 'yes') {
            $rrd_options .= " DEF:pollerRawX$index=$rrd_filename:poller:AVERAGE:start=$prev_from:end=$from";
            // change undefined to 0
            $rrd_options .= " CDEF:pollerX$index=pollerRawX$index,UN,0,pollerRawX$index,IF";
            $rrd_options .= " SHIFT:pollerX$index:$period";
            $cdefX[] = 'pollerX' . $index . $suffixX;
            $suffixX = ',+';
        }
    }
}

$rrd_options .= ' CDEF:poller=' . implode(',', $cdef);
$rrd_options .= ' CDEF:avgpol=poller,' . count($cdef) . ',/';
$rrd_options .= " 'COMMENT:Seconds      Cur     Min     Max     Avg     Avg device\\n'";
$rrd_options .= ' LINE1.25:poller#36393D:Poller';
$rrd_options .= ' GPRINT:poller:LAST:%6.2lf  GPRINT:poller:MIN:%6.2lf';
$rrd_options .= ' GPRINT:poller:MAX:%6.2lf  GPRINT:poller:AVERAGE:%6.2lf';
$rrd_options .= " 'GPRINT:avgpol:AVERAGE:%6.2lf\\n'";
if ($_GET['previous'] == 'yes') {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= ' CDEF:pollerX=' . implode(',', $cdefX);
    $rrd_options .= ' CDEF:avgpolX=pollerX,' . count($cdefX) . ',/';
    $rrd_options .= " LINE1.25:pollerX#CCCCCC:'Prev Poller'\t";
    $rrd_options .= ' GPRINT:pollerX:MIN:%6.2lf';
    $rrd_options .= ' GPRINT:pollerX:MAX:%6.2lf  GPRINT:pollerX:AVERAGE:%6.2lf';
    $rrd_options .= " 'GPRINT:avgpolX:AVERAGE:%6.2lf\\n'";
}
