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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Models\Device;
use LibreNMS\Config;

$scale_min = '0';
$colors = Config::get('graph_colours.manycolours');

require 'includes/graphs/common.inc.php';

$hostnames = Device::pluck('hostname');
$modules = array_keys(Config::get('poller_modules'));
sort($modules);

foreach ($modules as $module_index => $module) {
    $cdef = [];
    $suffix = '';

    foreach ($hostnames as $index => $hostname) {
        $rrd_filename = rrd_name($hostname, ['poller-perf', $module]);
        if (rrdtool_check_rrd_exists($rrd_filename)) {
            $rrd_options .= " DEF:{$module}Raw$index=$rrd_filename:poller:AVERAGE";
            // change undefined to 0
            $rrd_options .= " CDEF:$module$index={$module}Raw$index,UN,0,{$module}Raw$index,IF";

            $cdef[] = $module . $index . $suffix;
            $suffix = ',+';
        }
    }

    if (empty($cdef)) {
        //remove the module so we don't print it in the legend
        unset($modules[$module_index]);
    } else {
        // have data for this module, display it
        $rrd_options .= " CDEF:$module=" . implode(',', $cdef);
    }
}

$rrd_options .= " 'COMMENT:Seconds                 Cur     Min     Max      Avg\\n'";

foreach ($modules as $index => $module) {
    $color = $colors[$index % count($colors)];

    $rrd_options .= " AREA:$module#$color:'" . rrdtool_escape($module, 16) ."':STACK";

    $rrd_options .= " GPRINT:$module:LAST:%6.2lf  GPRINT:$module:MIN:%6.2lf";
    $rrd_options .= " GPRINT:$module:MAX:%6.2lf  'GPRINT:$module:AVERAGE:%6.2lf\\n'";
}
