#!/usr/bin/env php
<?php

/*
 * LibreNMS module to poll Nagios Services
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$init_modules = [];
require __DIR__ . '/includes/init.php';

$options = getopt('drfpgh:');
if (Debug::set(isset($options['d']))) {
    echo "DEBUG!\n";
}

$poller_start = microtime(true);

$datastore = Datastore::init($options);

echo "Starting service polling run:\n\n";
$polled_services = 0;

$where = '';
$params = [];
if ($options['h']) {
    if (is_numeric($options['h'])) {
        $where = 'AND `S`.`device_id` = ?';
        $params[] = (int) $options['h'];
    } else {
        if (preg_match('/\*/', $options['h'])) {
            $where = "AND `hostname` LIKE '?'";
            $params[] = str_replace('*', '%', $options['h']);
        } else {
            $where = "AND `hostname` = '?'";
            $params[] = $options['h'];
        }
    }
}

$sql = 'SELECT D.*,S.*,attrib_value  FROM `devices` AS D'
       . ' INNER JOIN `services` AS S ON S.device_id = D.device_id AND D.disabled = 0 ' . $where
       . ' LEFT JOIN `devices_attribs` as A ON D.device_id = A.device_id AND A.attrib_type = "override_icmp_disable"'
       . ' ORDER by D.device_id DESC;';

foreach (dbFetchRows($sql, $params) as $service) {
    // Run the polling function if service is enabled and the associated device is up, "Disable ICMP Test" option is not enabled,
    // or service hostname/ip is different from associated device
    if (! $service['service_disabled'] && ($service['status'] == 1 || ($service['status'] == 0 && $service['status_reason'] === 'snmp') ||
        $service['attrib_value'] === 'true' || ($service['service_ip'] !== $service['hostname'] &&
        $service['service_ip'] !== inet6_ntop($service['ip'])))) {
        poll_service($service);
        $polled_services++;
    } else {
        if (! $service['service_disabled']) {
            d_echo("\nService check - " . $service['service_id'] . "\nSkipping service check because device "
                . $service['hostname'] . " is down due to icmp.\n");
            Log::event(
                "Service check - {$service['service_desc']} ({$service['service_id']}) -
                Skipping service check because device {$service['hostname']} is down due to icmp",
                $service['device_id'],
                'service',
                4,
                $service['service_id']
            );
        } else {
            d_echo("\nService check - " . $service['service_id'] . "\nSkipping service check because device "
                . $service['service_type'] . " is disabled.\n");
        }
    }
}

$poller_end = microtime(true);
$poller_run = ($poller_end - $poller_start);
$poller_time = substr($poller_run, 0, 5);

$string = $argv[0] . ' ' . date(\LibreNMS\Config::get('dateformat.compact'))
    . " - $polled_services services polled in $poller_time secs";
d_echo("$string\n");

Datastore::terminate();
