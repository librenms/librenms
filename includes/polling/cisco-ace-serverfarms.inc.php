<?php

$serverfarm_array = snmpwalk_cache_oid($device, 'slbVServerInfoTable', array(), 'CISCO-SLB-MIB');
$serverfarm_db    = dbFetchRows('SELECT * FROM `loadbalancer_vservers` WHERE `device_id` = ?', array($device['device_id']));

foreach ($serverfarm_db as $vserver) {
    $classmaps[$vserver['classmap']] = $vserver;
}

foreach ($serverfarm_array as $index => $vserver) {
    $classmap    = str_replace('class-map-', '', $vserver['slbVServerClassMap']);
    $classmap_id = str_replace('9.', '', $index);

    $oids = array(
             'slbVServerNumberOfConnections',
             'slbVServerDroppedConnections',
             'slbVServerClientPacketCounts',
             'slbVServerClientByteCounts',
             'slbVServerPacketCounts',
             'slbVServerByteCounts',
            );

    $db_oids = array(
                $classmap_id      => 'classmap_id',
                $classmap         => 'classmap',
                'slbVServerState' => 'serverstate',
               );

    if (!is_array($classmaps[$classmap])) {
        $classmap_in = dbInsert(array('device_id' => $device['device_id'], 'classmap_id' => $classmap_id, 'classmap' => $classmap, 'serverstate' => $vserver['slbVServerState']), 'loadbalancer_vservers');
    }
    else {
        foreach ($db_oids as $db_oid => $db_value) {
            $db_update[$db_value] = $vserver[$db_oid];
        }

        $updated = dbUpdate($db_update, 'loadbalancer_vservers', '`classmap_id` = ?', $vserver['slbVServerState']['classmap']);
    }

    $rrd_file   = $config['rrd_dir'].'/'.$device['hostname'].'/vserver-'.$classmap_id.'.rrd';
    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds      = truncate(str_replace('slbVServer', '', $oid), 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:1000000000";
    }

    $fields = array();

    foreach ($oids as $oid) {
        if (is_numeric($vserver[$oid])) {
            $value = $vserver[$oid];
        }
        else {
            $value = '0';
        }
        $fields[$oid] = $value;
    }

    if (isset($classmaps[$classmap])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }
        rrdtool_update($rrd_file, $fields);
    }
}//end foreach

unset($oids, $oid, $vserver);
