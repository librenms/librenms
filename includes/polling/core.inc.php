<?php
/*
 * LibreNMS Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Time;

$snmpdata = snmp_get_multi_oid($device, ['sysUpTime.0', 'sysName.0', 'sysObjectID.0', 'sysDescr.0'], '-OQnUt', 'SNMPv2-MIB');

$poll_device['sysUptime'] = $snmpdata['.1.3.6.1.2.1.1.3.0'];
$poll_device['sysName'] = str_replace("\n", '', strtolower($snmpdata['.1.3.6.1.2.1.1.5.0']));
$poll_device['sysObjectID'] = $snmpdata['.1.3.6.1.2.1.1.2.0'];
$poll_device['sysDescr'] = str_replace(chr(218), "\n", $snmpdata['.1.3.6.1.2.1.1.1.0']);

if (! empty($agent_data['uptime'])) {
    [$uptime] = explode(' ', $agent_data['uptime']);
    $uptime = round($uptime);
    echo "Using UNIX Agent Uptime ($uptime)\n";
} else {
    $uptime_data = snmp_get_multi($device, ['snmpEngineTime.0', 'hrSystemUptime.0'], '-OQnUst', 'HOST-RESOURCES-MIB:SNMP-FRAMEWORK-MIB');

    $uptime = max(
        round($poll_device['sysUptime'] / 100),
        Config::get("os.{$device['os']}.bad_snmpEngineTime") ? 0 : $uptime_data[0]['snmpEngineTime'],
        Config::get("os.{$device['os']}.bad_hrSystemUptime") ? 0 : round($uptime_data[0]['hrSystemUptime'] / 100)
    );
    d_echo("Uptime seconds: $uptime\n");
}

if ($uptime != 0 && Config::get("os.{$device['os']}.bad_uptime") !== true) {
    if ($uptime < $device['uptime']) {
        log_event('Device rebooted after ' . Time::formatInterval($device['uptime']) . " -> {$uptime}s", $device, 'reboot', 4, $device['uptime']);
    }

    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('uptime', 'GAUGE', 0),
    ];
    data_update($device, 'uptime', $tags, $uptime);

    $os->enableGraph('uptime');

    echo 'Uptime: ' . Time::formatInterval($uptime) . PHP_EOL;

    $update_array['uptime'] = $uptime;
    $device['uptime'] = $uptime;
}//end if

// Save results of various polled values to the database
foreach (['sysObjectID', 'sysName', 'sysDescr'] as $elem) {
    if ($poll_device[$elem] != $device[$elem]) {
        $update_array[$elem] = $poll_device[$elem];
        $device[$elem] = $poll_device[$elem];
        log_event("$elem -> " . $poll_device[$elem], $device, 'system', 3);
    }
}

unset($snmpdata, $uptime_data, $uptime, $tags, $poll_device);
