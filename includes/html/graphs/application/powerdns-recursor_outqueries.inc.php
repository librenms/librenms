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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
include 'powerdns-recursor.inc.php';

$colours = 'mixed';
$unit_text = 'Queries/sec';

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'ds' => 'all-outqueries',
            'descr' => 'Total',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'ipv6-outqueries',
            'descr' => 'IPv6',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'tcp-outqueries',
            'descr' => 'TCP',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'throttled-out',
            'descr' => 'Throttled',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'outgoing-timeouts',
            'descr' => 'Timeouts',
            'area' => true,
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
