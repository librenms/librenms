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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

require 'includes/graphs/common.inc.php';

// escape % characters
$unit = preg_replace('/(?<!%)%(?!%)/', '%%', $unit);

$num = '%5.1lf'; // default: float
if (isset($unit_type)) {
    if ($unit_type == 'int') {
        $num = '%5.0lf';
    } elseif ($unit_type == 'si') {
        // replaces the manual unit
        $num .= '%s';
        $unit = '';
    }
}

if ($unit_long == $sensor['sensor_descr']) {
    $unit_long = '';
}

$col_w = 7 + strlen($unit);
$rrd_options .= " COMMENT:'". str_pad($unit_long, 35) . str_pad("Cur", $col_w). str_pad("Min", $col_w) . "Max\\n'";

$sensor_descr_fixed = rrdtool_escape($sensor['sensor_descr'], 28);

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'$sensor_descr_fixed'";

if ($scale_min >= 0) {
    $rrd_options .= " AREA:sensor#cc000055";
}

$rrd_options .= " GPRINT:sensor:LAST:$num$unit";
$rrd_options .= " GPRINT:sensor:MIN:$num$unit";
$rrd_options .= " GPRINT:sensor:MAX:$num$unit\\l";

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:'.$sensor['sensor_limit'].'#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:'.$sensor['sensor_limit_low'].'#999999::dashes';
}
