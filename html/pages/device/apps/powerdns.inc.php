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
 * @author     LibreNMS Contributors
*/

global $config;

$graphs = [
    'powerdns_latency'     => 'PowerDNS - Latency',
    'powerdns_fail'        => 'PowerDNS - Corrupt / Failed / Timed out',
    'powerdns_packetcache' => 'PowerDNS - Packet Cache',
    'powerdns_querycache'  => 'PowerDNS - Query Cache',
    'powerdns_recursing'   => 'PowerDNS - Recursing Queries and Answers',
    'powerdns_queries'     => 'PowerDNS - Total UDP/TCP Queries and Answers',
    'powerdns_queries_udp' => 'PowerDNS - Detail UDP IPv4/IPv6 Queries and Answers',
];

include 'app.bootstrap.inc.php';
