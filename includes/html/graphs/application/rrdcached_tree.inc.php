<?php
/**
 * rrdcached_tree.inc.php
 *
 * Generates a graph of the tree stats for rrdcached
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
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
include 'rrdcached.inc.php';

$colours = 'blues';

$rrd_list = [
    [
        'ds' => 'tree_depth',
        'filename' => $rrd_filename,
        'descr' => 'Tree Depth',
    ],
    [
        'ds' => 'tree_nodes_number',
        'filename' => $rrd_filename,
        'descr' => 'Tree Nodes',
    ],
];

require 'includes/html/graphs/generic_multi.inc.php';
