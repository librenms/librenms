<?php
/**
 * powerdns-recursor_cache_size.inc.php
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

$colours = 'purples';
$unit_text = 'Entries';

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'ds' => 'cache-entries',
            'descr' => 'Query Cache',
            'colour' => '202048',
        ),
        array(
            'filename' => $rrd_filename,
            'ds' => 'packetcache-entries',
            'descr' => 'Packet Cache',
            'colour' => 'CC7CCC',
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
