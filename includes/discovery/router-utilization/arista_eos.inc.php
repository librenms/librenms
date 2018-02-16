<?php

// ARISTA-HARDWARE-UTILIZATION-MIB aristaHardwareUtilizationMibObjects aristaHardwareUtilizationTable
// $router_utilization = snmpwalk_array_num($device, '.1.3.6.1.4.1.30065.3.22.1.1', 1);

$router_utilization = snmpwalk_cache_oid($device, 'aristaHardwareUtilizationTable', array(), 'ARISTA-HARDWARE-UTILIZATION-MIB');

$arista_mib['aristaHardwareUtilizationInUseEntries'] = 4;
$arista_mib['aristaHardwareUtilizationFreeEntries'] = 5;
$arista_mib['aristaHardwareUtilizationCommittedEntries'] = 6;
$arista_mib['aristaHardwareUtilizationMaxEntries'] = 7;
$arista_mib['aristaHardwareUtilizationHighWatermark'] = 8;
$arista_mib['aristaHardwareUtilizationHighWatermarkTime'] = 9;

foreach ($router_utilization as $key_oid => $value) {
    $label = explode('.', $key_oid);
    $measure['resource'] = $label[0];
    $measure['feature'] = $label[1];
    $measure['forwarding_element'] = $label[2];
    $current = $value['aristaHardwareUtilizationInUseEntries'];
    $maximum = $value['aristaHardwareUtilizationMaxEntries'];
    
    $base_oid = '.1.3.6.1.4.1.30065.3.22.1.1.1.';
    $end_oid  = string_to_oid($measure['resource']) . '.' . string_to_oid($measure['feature']) . '.' . string_to_oid($measure['forwarding_element']);

    dbInsert(
        array(
            'device_id'          => $device['device_id'],
            'oid_current'        => $base_oid . $arista_mib['aristaHardwareUtilizationInUseEntries'] . '.' . $end_oid,
            'oid_maximum'        => $base_oid . $arista_mib['aristaHardwareUtilizationMaxEntries'] . '.' . $end_oid,
            'feature'            => $measure['feature'],
            'resource'           => $measure['resource'],
            'forwarding_element' => $measure['forwarding_element'],
            'current'            => $current,
            'maximum'            => $maximum,
        ),
        'router_utilization'
    );
}
