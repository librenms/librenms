<?php
/**
 * powerdns-recursor_outqueries.inc.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

include 'powerdns-recursor.inc.php';

$colours = 'mixed';
$unit_text = 'Queries/sec';

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'ds' => 'all-outqueries',
            'descr' => 'Total',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'ipv6-outqueries',
            'descr' => 'IPv6',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'tcp-outqueries',
            'descr' => 'TCP',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'throttled-out',
            'descr' => 'Throttled',
            'area' => true,
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'outgoing-timeouts',
            'descr' => 'Timeouts',
            'area' => true,
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
