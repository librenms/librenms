<?php

if ($device['os_group'] == 'cisco') {
    $datas = SnmpQuery::walk('CISCO-IP-STAT-MIB::cipMacSwitchedBytes')->table(1);

    foreach ($datas as $data) {
        dd($data);
        $oid = str_replace('.1.3.6.1.4.1.9.9.84.1.2.1.1.4.', '', $oid);
        $mac = \LibreNMS\Util\Mac::parse($oid);
        dump($oid, $mac);
        [$if, $direction, $a_a, $a_b, $a_c, $a_d, $a_e, $a_f] = explode('.', $oid);
        $port = PortCache::getByIfIndex($if);
        $interface = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device['device_id'], $if]);

        if ($interface) {
            if (dbFetchCell('SELECT COUNT(*) from mac_accounting WHERE port_id = ? AND mac = ?', [$interface['port_id'], $mac])) {
                echo '.';
            } else {
                dbInsert(['port_id' => $interface['port_id'], 'mac' => $mac], 'mac_accounting');
                echo '+';
            }
        }
    }//end foreach

    echo "\n";
} //end if

// FIXME - NEEDS TO REMOVE STALE ENTRIES?? :O
