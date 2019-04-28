<?php
/**
 * rrdcached_journal.inc.php
 *
 * Generates a graph of journal stats for rrdcached
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

$colours = 'pinks';

$rrd_list = array(
    array(
        'ds' => 'journal_rotate',
        'filename' => $rrd_filename,
        'descr' => 'Journal Rotated',
    ),
    array(
        'ds' => 'journal_bytes',
        'filename' => $rrd_filename,
        'descr' => 'Journal Bytes Written',
    )
);

require 'includes/html/graphs/generic_multi.inc.php';
