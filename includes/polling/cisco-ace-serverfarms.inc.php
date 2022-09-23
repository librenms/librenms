<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
    $serverfarm_array = snmpwalk_cache_oid($device, 'slbVServerInfoTable', [], 'CISCO-SLB-MIB');
    $serverfarm_db = dbFetchRows('SELECT * FROM `loadbalancer_vservers` WHERE `device_id` = ?', [$device['device_id']]);

    foreach ($serverfarm_db as $vserver) {
        $classmaps[$vserver['classmap']] = $vserver;
    }

    foreach ($serverfarm_array as $index => $vserver) {
        $classmap = str_replace('class-map-', '', $vserver['slbVServerClassMap']);
        $classmap_id = str_replace('9.', '', $index);

        $oids = [
            'slbVServerNumberOfConnections',
            'slbVServerDroppedConnections',
            'slbVServerClientPacketCounts',
            'slbVServerClientByteCounts',
            'slbVServerPacketCounts',
            'slbVServerByteCounts',
        ];

        $db_oids = [
            $classmap_id => 'classmap_id',
            $classmap => 'classmap',
            'slbVServerState' => 'serverstate',
        ];

        if (! is_array($classmaps[$classmap])) {
            $classmap_in = dbInsert([
                'device_id' => $device['device_id'],
                'classmap_id' => $classmap_id,
                'classmap' => $classmap,
                'serverstate' => $vserver['slbVServerState'],
            ], 'loadbalancer_vservers');
        } else {
            foreach ($db_oids as $db_oid => $db_value) {
                $db_update[$db_value] = $vserver[$db_oid];
            }

            $updated = dbUpdate($db_update, 'loadbalancer_vservers', '`classmap_id` = ?', $vserver['slbVServerState']['classmap']);
        }

        $rrd_name = ['vserver', $classmap_id];
        $rrd_def = new RrdDefinition();
        foreach ($oids as $oid) {
            $oid_ds = str_replace('slbVServer', '', $oid);
            $rrd_def->addDataset($oid_ds, 'COUNTER', null, 1000000000);
        }

        $fields = [];
        foreach ($oids as $oid) {
            if (is_numeric($vserver[$oid])) {
                $value = $vserver[$oid];
            } else {
                $value = '0';
            }
            $fields[$oid] = $value;
        }

        if (isset($classmaps[$classmap])) {
            $tags = compact('classmap_id', 'rrd_name', 'rrd_def');
            data_update($device, 'vservers', $tags, $fields);
        }
    }//end foreach

    unset($rrd_name, $rrd_def, $oids, $oid, $vserver);
}

unset(
    $serverfarm_array,
    $serverfarm_db
);
