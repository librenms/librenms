<?php
/**
 * conntrack_igmp.inc.php
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
 */

$rrd = rrd_name($device['hostname'], array('app', 'conntrack', $app['app_id']));
if (rrdtool_check_rrd_exists($rrd)) {
    $rrd_filename = $rrd;
}

$colours = 'mixed';
$unit_text = 'IGMP';

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'ds' => 'igmp_u',
            'descr' => 'unreplied',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'igmp_ha',
            'descr' => 'half-assured',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'igmp_tot',
            'descr' => 'total',
            'area' => true,
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi_line.inc.php';
