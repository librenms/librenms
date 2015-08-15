<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Steve Calvário <https://github.com/Calvario/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'dsm') {
    echo 'DSM States';

    // System Status (Value : 1 Normal, 2 Failed)
    $system_status_oid = '.1.3.6.1.4.1.6574.1.1.0';
    $system_status     = snmp_get($device, $system_status_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $system_status_oid, 'SystemStatus', 'snmp', 'System Status', '1', '1', null, null, null, null, $system_status);


    // Power Status OID (Value : 1 Normal, 2 Failed)
    $power_status_oid = '.1.3.6.1.4.1.6574.1.3.0';
    $power_status     = snmp_get($device, $power_status_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $power_status_oid, 'PowerStatus', 'snmp', 'Power Status', '1', '1', null, null, null, null, $power_status);


    // System Fan Status OID (Value : 1 Normal, 2 Failed)
    $system_fan_status_oid = '.1.3.6.1.4.1.6574.1.4.1.0';
    $system_fan_status     = snmp_get($device, $system_fan_status_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $system_fan_status_oid, 'SystemFanStatus', 'snmp', 'System Fan Status', '1', '1', null, null, null, null, $system_fan_status);


    // CPU Fan Status OID (Value : 1 Normal, 2 Failed)
    $cpu_fan_status_oid = '.1.3.6.1.4.1.6574.1.4.2.0';
    $cpu_fan_status     = snmp_get($device, $cpu_fan_status_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $cpu_fan_status_oid, 'CPUFanStatus', 'snmp', 'CPU Fan Status', '1', '1', null, null, null, null, $cpu_fan_status);


    // UPS Status OID (Value : OL - On Line, OB - On Battery)
    $ups_status_oid = '.1.3.6.1.4.1.6574.4.2.1.0';
    $ups_status     = snmp_get($device, $ups_status_oid, '-Oqv');
    // Temp because only support INT
    if ((string) $ups_status == '"OL"') {
        $ups_status = 1;
    }
    else {
        $ups_status = 2;
    }

    discover_sensor($valid['sensor'], 'state', $device, $ups_status_oid, 'UPSStatus', 'snmp', 'UPS Status', '1', '1', null, null, null, null, $ups_status);


    // DSM Upgrade Available OID (Value : 1 Available, 2 Unavailable, 3 Connecting, 4 Disconnected, 5 Others)
    $dsm_upgrade_available_oid = '.1.3.6.1.4.1.6574.1.5.4.0';
    $dsm_upgrade_available     = snmp_get($device, $dsm_upgrade_available_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $dsm_upgrade_available_oid, 'DSMUpgradeStatus', 'snmp', 'DSM Upgrade Status', '1', '1', null, null, null, null, $dsm_upgrade_available);


    // RAID Status OID (Value : 1 Normal, 2 Repairing, 3 Migrating, 4 Expanding, 5 Deleting, 6 Creating, 7 RaidSyncing, 8 RaidParityChecking, 9 RaidAssembling, 10 Canceling, 11 Degrade, 12 Crashed)
    $raid_status_oid = '.1.3.6.1.4.1.6574.3.1.1.3';
    $raid_status     = snmp_get($device, $raid_status_oid, '-Oqv');
    discover_sensor($valid['sensor'], 'state', $device, $raid_status_oid, 'RAIDStatus', 'snmp', 'RAID Status', '1', '1', null, null, null, null, $raid_status);


    // Disks Status OID (Value : 1 Normal, 2 Initialized, 3 Not Initialized, 4 System Partition Failed, 5 Crashed )
    $disks_status_oid = '.1.3.6.1.4.1.6574.2.1.1.5.';
    $disks            = snmpwalk_cache_multi_oid($device, 'diskTable', array(), 'SYNOLOGY-DISK-MIB');
    if (is_array($disks)) {
        foreach ($disks as $disk_number => $entry) {
            $disk_oid         = $disks_status_oid.$disk_number;
            $disk_status      = $entry['diskStatus'];
            $disk_information = $entry['diskID'].' '.$entry['diskModel'].' Status';
            discover_sensor($valid['sensor'], 'state', $device, $disk_oid, 'DiskStatus'.$disk_number, 'snmp', $disk_information, '1', '1', null, null, null, null, $disk_status);
        }
    }
}//end if
