<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     Daniel Preussker <f0o@devilcode.org>, Zane C. Bowers-Hadley <vvelox@vvelox.net>
*/

global $config;

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

include "app.bootstrap.inc.php";
