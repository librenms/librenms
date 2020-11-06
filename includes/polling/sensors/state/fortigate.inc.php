<?php

if ($device['os'] == 'fortigate') {
    if (in_array($sensor['sensor_type'], ['clusterState', 'haSyncStatus'])) {
        // Fetch the cluster members and their data
        $fgHaStatsEntryOid = '.1.3.6.1.4.1.12356.101.13.2.1.1';
        $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsEntryOid, [], 'FORTINET-FORTIGATE-MIB');

        if ($sensor['sensor_type'] == 'clusterState') {
            // Determine if the cluster contains more than 1 device
            $clusterState = 0;
            if (is_array($haStatsEntries)) {
                $clusterMemberCount = count($haStatsEntries);
                $clusterState = $clusterMemberCount == 1 ? 0 : 1;
            }

            $sensor_value = $clusterState;
        } elseif ($sensor['sensor_type'] == 'haSyncStatus') {
            $clusterState = 0;
            // 0 = unsynchronized, 1 = synchronized
            $synchronized = 1;
            foreach ($haStatsEntries as $entry) {
                if ($entry['fgHaStatsSyncStatus'] == 'unsynchronized') {
                    $synchronized = 0;
                }
            }
            dd($synchronized);
            $sensor_value = $synchronized;
        }

        unset($fgHaStatsEntryOid, $haStatsEntries, $clusterMemberCount, $synchronized, $clusterState);
    }

}
