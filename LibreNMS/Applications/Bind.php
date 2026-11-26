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

namespace LibreNMS\Applications;

class Bind extends ApplicationDefinition
{
    public const string TYPE = 'bind';
    protected const ?string DOCS = 'BIND9 aka named';
    protected const ?string NAME = 'BIND';

    protected const ?array OVERVIEW = [
        'incoming',
        'outgoing',
        'rr_positive',
        'rr_negative',
        'rtt',
        'resolver_failure',
        'resolver_qrs',
        'resolver_naf',
        'server_received',
        'server_results',
        'server_issues',
        'cache_hm',
        'adb_in',
        'sockets_active',
        'sockets_errors',
    ];

    protected const array GRAPHS = [
        'incoming' => 'Incoming',
        'outgoing' => 'Outgoing',
        'rr_positive' => 'RR Sets Positive',
        'rr_negative' => 'RR Sets Negative',
        'rtt' => 'Resolver RTT',
        'resolver_failure' => 'Resolver Failures',
        'resolver_qrs' => 'Resolver Queries Sent/Received',
        'resolver_naf' => 'NS Query Status',
        'server_received' => 'Server Queries/Requests Received',
        'server_results' => 'Server Results',
        'server_issues' => 'Server Issues',
        'cache_hm' => 'Cache Hits & Misses',
        'cache_tree' => 'Cache Tree Memory',
        'cache_heap' => 'Cache Heap Memory',
        'cache_deleted' => 'Cache Record Deletion',
        'adb_size' => 'Address & Name Hash Table Size',
        'adb_in' => 'Address & Name In Hash Table',
        'sockets_active' => 'Active Sockets',
        'sockets_errors' => 'Socket Errors Per Second',
        'sockets_opened' => 'Opened Sockets Per Second',
        'sockets_closed' => 'Closed Sockets Per Second',
        'sockets_bf' => 'Socket Bind Failures Per Second',
        'sockets_cf' => 'Socket Connect Failures Per Second',
        'sockets_established' => 'Connections Established Per Second',
    ];
}
