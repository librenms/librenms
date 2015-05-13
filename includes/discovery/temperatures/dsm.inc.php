<?php

if ($device['os'] == 'dsm') {
    echo "DSM ";
    $oid = '2';
    $temp_oid = '.1.3.6.1.4.1.6574.1.2';
    $current = snmp_get($device,$temp_oid,"-Oqv");
    discover_sensor($valid['sensor'], 'temperature', $device, $temp_oid, $oid, 'snmp', 'System Temperature', '1', '1', NULL, NULL, NULL, NULL, $current);

    $disks = snmpwalk_cache_multi_oid($device, "diskTable", array(), "SYNOLOGY-DISK-MIB");
    if (is_array($disks)) {
        $cur_oid = '.1.3.6.1.4.1.6574.2.1.1.6.';
        foreach ($disks as $index => $entry) {
            $oid = $index;
            $disk_oid = $cur_oid.$index;
            $current = $entry['diskTemperature'];
            $descr = $entry['diskID'] . ' ' . $entry['diskModel'];
            discover_sensor($valid['sensor'], 'temperature', $device, $disk_oid, $oid, 'snmp', $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
        }
    }
}
