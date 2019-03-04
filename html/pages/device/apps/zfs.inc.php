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
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @subpackage webui
* @link       http://librenms.org
* @copyright  2019 LibreNMS
* @author     LibreNMS Contributors
*/

global $config;

$pools = get_zfs_pools($device['device_id']);

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'zfs',
];

print_optionbar_start();

echo generate_link('ARC', $link_array);
echo '| Pools:';
$pool_int = 0;
while (isset($pools[$pool_int])) {
    $pool = $pools[$pool_int];
    $label = $pool;

    if ($vars['pool'] == $pool) {
        $label = '>>' . $pool . '<<';
    }

    $pool_int++;

    $append = '';
    if (isset($pools[$pool_int])) {
        $append = ', ';
    }

    echo generate_link($label, $link_array, array('pool' => $pool)) . $append;
}

print_optionbar_end();

if (!isset($vars['pool'])) {
    $graphs = [
        'zfs_arc_misc' => 'ARC misc',
        'zfs_arc_size' => 'ARC size in bytes',
        'zfs_arc_size_per' => 'ARC size, percent of max size',
        'zfs_arc_size_breakdown' => 'ARC size breakdown',
        'zfs_arc_efficiency' => 'ARC efficiency',
        'zfs_arc_cache_hits_by_list' => 'ARC cache hits by list',
        'zfs_arc_cache_hits_by_type' => 'ARC cache hits by type',
        'zfs_arc_cache_misses_by_type' => 'ARC cache misses by type',
        'zfs_arc_cache_hits' => 'ARC cache hits',
        'zfs_arc_cache_miss' => 'ARC cache misses',
    ];
} else {
    $graphs = [
        'zfs_pool_space' => 'Pool Space',
        'zfs_pool_cap' => 'Pool Capcity',
        'zfs_pool_frag' => 'Pool Fragmentation',
    ];
}

include "app.bootstrap.inc.php";
