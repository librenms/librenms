<?php

if ($device['os'] == 'fortigate') {
    if ($sensor['sensor_type'] == 'clusterState') {
        // Fetch the cluster members
        $fgHaStatsEntryOid = '.1.3.6.1.4.1.12356.101.13.2.1.1';
        $haStats = snmpwalk_cache_multi_oid($device, $fgHaStatsEntryOid, [], 'FORTINET-FORTIGATE-MIB');

        $clusterState = 0;

        if (is_array($haStats)) {
            $clusterMemberCount = count($haStats);
            $clusterState = $clusterMemberCount == 1 ? 0 : 1;
        }

        $sensor_value = $clusterState;
        unset($fgHaStatsEntryOid, $haStats, $clusterMemberCount, $clusterState );
    }
}
