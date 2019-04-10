<?php
/**
 * rrdcached_events.inc.php
 *
 * Generates a combined graph of events for rrdcached
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

include 'rrdcached.inc.php';

$nototal = 1;
$colours = 'mixed';

$rrd_list = array(
    array (
        'ds' => 'updates_written',
        'filename' => $rrd_filename,
        'descr' => 'Updates Written',
    ),
    array (
        'ds' => 'data_sets_written',
        'filename' => $rrd_filename,
        'descr' => 'Data Sets Written',
    ),
    array(
        'ds' => 'updates_received',
        'filename' => $rrd_filename,
        'descr' => 'Updates Received',
    ),
    array (
        'ds' => 'flushes_received',
        'filename' => $rrd_filename,
        'descr' => 'Flushes Received',
    ),
);

require 'includes/html/graphs/generic_multi_line.inc.php';
