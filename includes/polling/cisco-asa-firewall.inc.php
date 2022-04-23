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

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco' && ($device['os'] == 'asa' || $device['os'] == 'ftd') && $device['type'] == 'firewall') {
    $oid_list = 'cfwConnectionStatValue.protoIp.currentInUse';
    $temp_data = snmpwalk_cache_double_oid($device, $oid_list, [], 'CISCO-FIREWALL-MIB');
    foreach ($temp_data as $oid => $result) {
        $oid = substr(strchr($oid, '.'), 1);
        $data[$oid]['data'] = $result['cfwConnectionStatValue'];
        $asa_db = dbFetchCell('SELECT `ciscoASA_id` FROM `ciscoASA` WHERE `device_id` = ? AND `oid` = ?', [$device['device_id'], $oid]);
        if (! is_numeric($asa_db)) {
            $asa_db = dbInsert(['device_id' => $device['device_id'], 'oid' => $oid, 'data' => $result['cfwConnectionStatValue']], 'ciscoASA');
        } else {
            $asa_db = dbUpdate(['data' => $result['cfwConnectionStatValue']], 'ciscoASA', 'device_id=?', [$device['device_id']]);
        }

        $data[$oid]['db_id'] = $asa_db;
    }

    if ($data['currentInUse']) {
        $rrd_def = RrdDefinition::make()->addDataset('connections', 'GAUGE', 0);
        $fields = [
            'connections' => $data['currentInUse']['data'],
        ];

        $tags = compact('rrd_def');
        data_update($device, 'asa_conns', $tags, $fields);

        $os->enableGraph('asa_conns');
        echo ' ASA Connections';
    }

    unset($data, $rrd_def);
}//end if
