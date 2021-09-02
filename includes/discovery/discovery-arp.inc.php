<?php

//
// LibreNMS module to do device discovery by ARP table contents.
//
// Needs to be run after the ARP table discovery, because it uses the
// data gathered by the ARP table discovery module.  Keeps a cache of
// seen hosts, and will not attempt re-discovery of the same IP (whether
// discovery failed or succeed) during the same discovery run.
//
// Copyright (c) 2012-2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
//
// Author:  Paul Gear <librenms@libertysys.com.au>
// License: GPLv3
//

use LibreNMS\Config;

$hostname = $device['hostname'];
$deviceid = $device['device_id'];

// Find all IPv4 addresses in the MAC table that haven't been discovered on monitored devices.
$sql = '
    SELECT *
    FROM ipv4_mac as m, ports as i
    WHERE m.port_id = i.port_id
    AND i.device_id = ?
    AND i.deleted = 0
    AND NOT EXISTS (
        SELECT * FROM ipv4_addresses a
        WHERE a.ipv4_address = m.ipv4_address
    )
    GROUP BY ipv4_address
    ORDER BY ipv4_address
    ';

// FIXME: Observium now uses ip_mac.ip_address in place of ipv4_mac.ipv4_address - why?
$names = [];
$ips = [];

foreach (dbFetchRows($sql, [$deviceid]) as $entry) {
    $ip = $entry['ipv4_address'];
    $mac = $entry['mac_address'];
    $if = $entry['port_id'];
    $int = cleanPort($if);
    $label = $int['label'];

    // Even though match_network is done inside discover_new_device, we do it here
    // as well in order to skip unnecessary reverse DNS lookups on discovered IPs.
    if (match_network(Config::get('autodiscovery.nets-exclude'), $ip)) {
        echo 'x';
        continue;
    }

    if (! match_network(Config::get('nets'), $ip)) {
        echo 'i';
        log_event("Ignored $ip", $deviceid, 'interface', 3, $if);
        continue;
    }

    // Attempt discovery of each IP only once per run.
    if (object_is_cached('arp_discovery', $ip)) {
        echo '.';
        continue;
    }

    object_add_cache('arp_discovery', $ip);

    $name = gethostbyaddr($ip);
    echo '+';
    $names[] = $name;
    $ips[$name] = $ip;
}

echo "\n";

// Run device discovery on each of the devices we've detected so far.
foreach ($names as $name) {
    $remote_device_id = discover_new_device($name, $device, 'ARP');
}

unset($names);
unset($ips);
