<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ fua http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == 'cisco' && $device['os'] == 'asa' && $device['type'] == 'firewall') {
    $oid_list  = 'cfwConnectionStatValue.protoIp.currentInUse';
    $temp_data = snmpwalk_cache_double_oid($device, $oid_list, array(), 'CISCO-FIREWALL-MIB');
    foreach ($temp_data as $oid => $result) {
        $oid                = substr(strchr($oid, '.'), 1);
        $data[$oid]['data'] = $result['cfwConnectionStatValue'];
        $asa_db             = dbFetchCell('SELECT `ciscoASA_id` FROM `ciscoASA` WHERE `device_id` = ? AND `oid` = ?', array($device['device_id'], $oid));
        if (!is_numeric($asa_db)) {
            $asa_db = dbInsert(array('device_id' => $device['device_id'], 'oid' => $oid, 'data' => $result['cfwConnectionStatValue']), 'ciscoASA');
        }
        else {
            $asa_db = dbUpdate(array('data' => $result['cfwConnectionStatValue']), 'ciscoASA', 'device_id=?', array($device['device_id']));
        }

        $data[$oid]['db_id'] = $asa_db;
    }

    $rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('asa_conns.rrd');

    $rrd_create .= ' DS:connections:GAUGE:600:0:U';
    $rrd_create .= $config['rrd_rra'];

    if (is_file($rrd_filename) || $data['currentInUse']) {
        if (!file_exists($rrd_filename)) {
            rrdtool_create($rrd_filename, $rrd_create);
        }

        $fields = array(
            'connections' => $data['currentInUse']['data'],
        );

        rrdtool_update($rrd_filename, $fields);

        $tags = array();
        influx_update($device,'asa_conns',$tags,$fields);

        $graphs['asa_conns'] = true;
        echo ' ASA Connections';
    }

    unset($data,$rrd_filename,$rrd_create);
}//end if
