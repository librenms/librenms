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
    $connections = SnmpQuery::get('CISCO-FIREWALL-MIB::cfwConnectionStatValue.protoIp.currentInUse')->value();

    if ($connections) {
        DB::table('ciscoASA')->updateOrInsert([
            'device_id' => $device['device_id'],
            'oid' => 'currentInUse',
        ], [
            'data' => $connections,
        ]);

        $rrd_def = RrdDefinition::make()->addDataset('connections', 'GAUGE', 0);
        $fields = [
            'connections' => $connections,
        ];

        $tags = compact('rrd_def');
        data_update($device, 'asa_conns', $tags, $fields);

        $os->enableGraph('asa_conns');
        echo ' ASA Connections';
    }

    unset($connections, $data, $rrd_def, $fields, $tags);
}//end if
