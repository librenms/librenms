<?php
/**
 * wireless-sensor.inc.php
 *
 * Common file for Wireless Sensor Graphs
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
require 'includes/html/graphs/common.inc.php';

// escape % characters
$unit = preg_replace('/(?<!%)%(?!%)/', '%%', $unit);

if ($unit_long == $sensor['sensor_descr']) {
    $unit_long = '';
}

$col_w = 7 + strlen($unit);
$sensor_descr_fixed = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 28);

$rrd_options .= " COMMENT:'" . str_pad($unit_long, 35) . str_pad('Cur', $col_w) . str_pad('Min', $col_w) . "Max\\n'";
$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";

$num = '%5.2lf'; // default: float
$output_def = 'sensor';
$factor = 1;
if ($unit === '') {
    $num = '%5.0lf';
} elseif ($unit == 'bps') {
    $num = '%5.3lf%s';
} elseif ($unit == 'Hz') {
    $num = '%5.3lf%s';
    $factor = 1000000;
    $output_def = 'sensorhz';
    $rrd_options .= " CDEF:$output_def=sensor,$factor,*";
} elseif ($unit == 'm') {
    $num = '%5.3lf%s';
    $factor = 1000;
    $output_def = 'sensorm';
    $rrd_options .= " CDEF:$output_def=sensor,$factor,*";
}

$rrd_options .= " LINE1.5:$output_def#0000cc:'$sensor_descr_fixed'";

if (isset($scale_min) && $scale_min >= 0) {
    $rrd_options .= " AREA:$output_def#0000cc55";
}

// ---- limits ----

if ($vars['width'] > 300) {
    if (is_numeric($sensor['sensor_limit'])) {
        $rrd_options .= ' LINE1:' . $sensor['sensor_limit'] * $factor . '#cc000060::dashes';
    }

    if (is_numeric($sensor['sensor_limit_low'])) {
        $rrd_options .= ' LINE1:' . $sensor['sensor_limit_low'] * $factor . '#cc000060::dashes';
    }
}

// ---- legend ----

$rrd_options .= " GPRINT:$output_def:LAST:'$num$unit'";
$rrd_options .= " GPRINT:$output_def:MIN:'$num$unit'";
$rrd_options .= " GPRINT:$output_def:MAX:'$num$unit'\\l";
