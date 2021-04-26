<?php
/**
 * airflow.inc.php
 *
 * LibreNMS airflow graph for WebUI
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$scale_min = '0';
$scale_max = '10';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                            Min   Max   Last\\n'";
$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 21) . "'";
$rrd_options .= ' GPRINT:sensor:MIN:%4.1lf';
$rrd_options .= ' GPRINT:sensor:MAX:%4.1lf';
$rrd_options .= ' GPRINT:sensor:LAST:%4.1lf\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
