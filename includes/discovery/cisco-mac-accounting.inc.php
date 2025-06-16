<?php

use App\Models\MacAccounting;
use LibreNMS\Util\Mac;

if ($device['os_group'] == 'cisco') {
    $snmpResponse = SnmpQuery::walk('CISCO-IP-STAT-MIB::cipMacSwitchedBytes');
    $datas = $snmpResponse->table(3);

    foreach ($datas as $ifIndex => $port_data) {
        foreach ($port_data as $direction => $direction_data) {
            foreach ($direction_data as $mac => $data) {
                $mac = Mac::parse($mac);
                $port_id = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

                if ($port_id) {
                    if (MacAccounting::where('port_id', $port_id)->where('mac', $mac)->exists()) {
                        echo '.';
                    } else {
                        MacAccounting::create([
                            'port_id' => $port_id,
                            'mac' => $mac,
                        ]);
                        echo '+';
                    }
                }
            }
        }
    }

    echo "\n";
} //end if

// FIXME - NEEDS TO REMOVE STALE ENTRIES?? :O
