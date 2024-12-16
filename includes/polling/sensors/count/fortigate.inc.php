<?php

// Convert string with textual date to remaining days for license expiration
if (preg_match("/fgLicContractExpiry\.\d+/", $sensor['sensor_index'])) {
    $expirationRaw = snmp_get($device, $sensor['sensor_oid'], '-OUqnv');
    $expirationDate = strtotime($expirationRaw);
    $sensor_value = round((strtotime($expirationRaw) - strtotime('now')) / 86400, 0);
}

if ($sensor['sensor_type'] === 'clusterState') {
    // Contains the indexes of all the cluster members
    $fgHaStatsIndex_num = '.1.3.6.1.4.1.12356.101.13.2.1.1.1';
    $fgHaStatsIndex_txt = 'fgHaStatsIndex';

    // Fetch the cluster members
    $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsIndex_txt, [], 'FORTINET-FORTIGATE-MIB');

    // Count of results is the amount of cluster members
    $sensor_value = count($haStatsEntries);
}

unset(
    $fgHaStatsIndex_num,
    $fgHaStatsIndex_txt,
    $haStatsEntries,
);
