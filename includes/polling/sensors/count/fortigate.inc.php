<?php

// Convert string with textual date to remaining days for license expiration
if (preg_match("/fgLicContractExpiry\.\d+/", $sensor['sensor_index'])) {
    $expirationRaw = SnmpQuery::get($sensor['sensor_oid'])->value(0);
    $expirationDate = strtotime($expirationRaw);
    $sensor_value = round((strtotime($expirationRaw) - strtotime('now')) / 86400, 0);
}

if ($sensor['sensor_type'] === 'clusterState') {
    // Contains the indexes of all the cluster members
    $fgHaStatsIndex_txt = 'fgHaStatsIndex';

    // Fetch the cluster members
    $haStatsEntries = SnmpQuery::walk('FORTINET-FORTIGATE-MIB::' . $fgHaStatsIndex_txt)->table(1);

    // Count of results is the amount of cluster members
    $sensor_value = count($haStatsEntries);
}

unset(
    $expirationRaw,
    $expirationDate,
    $fgHaStatsIndex_txt,
    $haStatsEntries,
);
