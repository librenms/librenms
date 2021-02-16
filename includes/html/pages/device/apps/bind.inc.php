<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 */

/*
 * Bind9 Application
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Apps
 * @updated in 2017 by Zane C. Bowers-Hadley <vvelox@vvelox.net>
 */

$graphs = [
    'bind_incoming' => 'Incoming',
    'bind_outgoing' => 'Outgoing',
    'bind_rr_positive' => 'RR Sets Positive',
    'bind_rr_negative' => 'RR Sets Negative',
    'bind_rtt' => 'Resolver RTT',
    'bind_resolver_failure' => 'Resolver Failures',
    'bind_resolver_qrs' => 'Resolver Queries Sent/Received',
    'bind_resolver_naf' => 'NS Query Status',
    'bind_server_received' => 'Server Queries/Requests Received',
    'bind_server_results' => 'Server Results',
    'bind_server_issues' => 'Server Issues',
    'bind_cache_hm' => 'Cache Hits & Misses',
    'bind_cache_tree' => 'Cache Tree Memory',
    'bind_cache_heap' => 'Cache Heap Memory',
    'bind_cache_deleted' => 'Cache Record Deletion',
    'bind_adb_size' => 'Address & Name Hash Table Size',
    'bind_adb_in' => 'Address & Name In Hash Table',
    'bind_sockets_active' => 'Active Sockets',
    'bind_sockets_errors' => 'Socket Errors Per Second',
    'bind_sockets_opened' => 'Opened Sockets Per Second',
    'bind_sockets_closed' => 'Closed Sockets Per Second',
    'bind_sockets_bf' => 'Socket Bind Failures Per Second',
    'bind_sockets_cf' => 'Socket Connect Failures Per Second',
    'bind_sockets_established' => 'Connections Established Per Second',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
