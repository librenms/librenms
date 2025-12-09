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

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Enum\Severity;
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

$query = Device::query()->select('devices.*', 'services.*')
    ->join('services', 'devices.device_id', '=', 'services.device_id')
    ->where('devices.disabled', 0)
    ->orderBy('devices.device_id', 'DESC');

if (isset($options['h'])) {
    if (is_numeric($options['h'])) {
        $query->where('services.device_id', $options['h']);
    } else {
        if (preg_match('/\*/', $options['h'])) {
            $query->where('devices.hostname', 'like', str_replace('*', '%', $options['h']));
        } else {
            $query->where('devices.hostname', $options['h']);
        }
    }
} else {
    $scheduler = LibrenmsConfig::get('schedule_type.services');
    if ($scheduler != 'legacy' && $scheduler != 'cron') {
        if (Debug::isEnabled()) {
            echo "Services are not enabled for cron scheduling\n";
        }
        exit(0);
    }
}

$services = $query->get();

foreach ($services as $service) {
    // Run the polling function if service is enabled and the associated device is up, "Disable ICMP Test" option is not enabled,
    // or service hostname/ip is different from associated device
    if (! $service['service_disabled'] && ($service['status'] == 1 || ($service['status'] == 0 && $service['status_reason'] === 'snmp') ||
        $service->getAttrib('override_icmp_disable') === 'true' || (! is_null($service['service_ip']) && $service['service_ip'] !== $service['hostname'] &&
        $service['service_ip'] !== inet6_ntop($service['ip'])))) {
        poll_service($service);
        $polled_services++;
    } else {
        if (! $service['service_disabled']) {
            d_echo("\nService check - " . $service['service_id'] . "\nSkipping service check because device "
                . $service['hostname'] . " is down due to icmp.\n");
            \App\Models\Eventlog::log(
                "Service check - {$service['service_desc']} ({$service['service_id']}) -
                Skipping service check because device {$service['hostname']} is down due to icmp",
                $service['device_id'],
                'service',
                Severity::Warning,
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

$string = $argv[0] . ' ' . date(\App\Facades\LibrenmsConfig::get('dateformat.compact'))
    . " - $polled_services services polled in $poller_time secs";
d_echo("$string\n");

app('Datastore')->terminate();
