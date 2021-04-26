<?php
/**
 * powerdns-recursor_cache_performance.inc.php
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
$unit_text = 'Packets/sec';

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'ds' => 'cache-hits',
            'descr' => 'Query Cache Hits',
            'colour' => '297159',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'cache-misses',
            'descr' => 'Query Cache Misses',
            'colour' => '73AC61',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'packetcache-hits',
            'descr' => 'Packet Cache Hits',
            'colour' => 'BC7049',
            'area' => true,
        ],
        [
            'filename' => $rrd_filename,
            'ds' => 'packetcache-misses',
            'descr' => 'Packet Cache Misses',
            'colour' => 'C98F45',
            'area' => true,
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
