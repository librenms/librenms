<?php
/**
 * poller_modules_perf.inc.php
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
use LibreNMS\Config;

$scale_min = '0';
$colors = Config::get('graph_colours.manycolours');

require 'includes/html/graphs/common.inc.php';

$hostnames = Device::pluck('hostname');
$modules = array_keys(Config::get('poller_modules'));
sort($modules);

foreach ($modules as $module_index => $module) {
    $cdef = [];
    $suffix = '';
    $cdefX = [];
    $suffixX = '';

    foreach ($hostnames as $index => $hostname) {
        $rrd_filename = Rrd::name($hostname, ['poller-perf', $module]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_options .= " DEF:{$module}Raw$index=$rrd_filename:poller:AVERAGE";
            // change undefined to 0
            $rrd_options .= " CDEF:$module$index={$module}Raw$index,UN,0,{$module}Raw$index,IF";

            $cdef[] = $module . $index . $suffix;
            $suffix = ',+';
            if ($_GET['previous'] == 'yes') {
                $rrd_options .= " DEF:{$module}RawX$index=$rrd_filename:poller:AVERAGE:start=$prev_from:end=$from";
                // change undefined to 0
                $rrd_options .= " CDEF:{$module}X$index={$module}RawX$index,UN,0,{$module}RawX$index,IF";
                $rrd_options .= " SHIFT:{$module}X$index:$period";
                $cdefX[] = $module . 'X' . $index . $suffixX;
                $suffixX = ',+';
            }
        }
    }

    if (empty($cdef)) {
        //remove the module so we don't print it in the legend
        unset($modules[$module_index]);
    } else {
        // have data for this module, display it
        $rrd_options .= " CDEF:$module=" . implode(',', $cdef);
        if ($_GET['previous']) {
            $rrd_options .= " CDEF:{$module}X=" . implode(',', $cdefX);
        }
    }
}

$rrd_options .= " 'COMMENT:Seconds                 Cur     Min     Max      Avg'";

if ($_GET['previous']) {
    $rrd_options .= " COMMENT:' \t    P Min   P Max   P Avg'";
}

$rrd_options .= " COMMENT:'\\n'";

foreach ($modules as $index => $module) {
    $color = $colors[$index % count($colors)];
    $rrd_options .= " AREA:$module#$color:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($module, 16) . "':STACK";
    $rrd_options .= " GPRINT:$module:LAST:%6.2lf  GPRINT:$module:MIN:%6.2lf";
    $rrd_options .= " GPRINT:$module:MAX:%6.2lf  'GPRINT:$module:AVERAGE:%6.2lf'";
    if ($_GET['previous']) {
        $rrd_options .= ' AREA:' . $module . 'X#99999999' . $stacked['transparency'] . ':';
        $rrd_options .= ' LINE1.25:' . $module . 'X#666666:';
        $rrd_options .= " COMMENT:'\t'";
        $rrd_options .= " GPRINT:{$module}X:MIN:%6.2lf";
        $rrd_options .= " GPRINT:{$module}X:MAX:%6.2lf";
        $rrd_options .= " GPRINT:{$module}X:AVERAGE:%6.2lf";
    }
    $rrd_options .= " COMMENT:'\\n'";
}
