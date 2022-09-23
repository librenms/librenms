<?php

if ($device['os_group'] == 'cisco') {
    $datas = snmp_walk($device, 'cipMacSwitchedBytes', '-Oqn', 'CISCO-IP-STAT-MIB');

    foreach (explode("\n", $datas) as $data) {
        [$oid] = explode(' ', $data);
        $oid = str_replace('.1.3.6.1.4.1.9.9.84.1.2.1.1.4.', '', $oid);
        [$if, $direction, $a_a, $a_b, $a_c, $a_d, $a_e, $a_f] = explode('.', $oid);
        unset($interface);
        $interface = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device['device_id'], $if]);
        $ah_a = zeropad(dechex($a_a));
        $ah_b = zeropad(dechex($a_b));
        $ah_c = zeropad(dechex($a_c));
        $ah_d = zeropad(dechex($a_d));
        $ah_e = zeropad(dechex($a_e));
        $ah_f = zeropad(dechex($a_f));
        $mac = "$ah_a$ah_b$ah_c$ah_d$ah_e$ah_f";

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
