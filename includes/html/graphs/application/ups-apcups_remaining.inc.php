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
* @copyright  2016 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/
require 'includes/html/graphs/common.inc.php';
$scale_min = 0;
$ds = 'time_remaining';
$colour_area = 'FF000011';
$colour_line = 'FF0000';
$colour_area_max = 'FFEE99';
$graph_max = 0;
$unit_text = 'Minutes';
$ups_apcups = Rrd::name($device['hostname'], ['app', 'ups-apcups', $app->app_id]);
if (Rrd::checkRrdExists($ups_apcups)) {
    $rrd_filename = $ups_apcups;
}
require 'includes/html/graphs/generic_simplex.inc.php';
