<?php

use Illuminate\Support\Str;

if ($device['os_group'] == 'cisco') {
    $datas = snmp_walk($device, 'cipMacSwitchedBytes', '-Oqn', 'CISCO-IP-STAT-MIB');

    foreach (explode("\n", $datas) as $data) {
        [$oid] = explode(' ', $data);
        $oid = str_replace('.1.3.6.1.4.1.9.9.84.1.2.1.1.4.', '', $oid);
        [$if, $direction, $a_a, $a_b, $a_c, $a_d, $a_e, $a_f] = explode('.', $oid);
        unset($interface);
        $interface = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device['device_id'], $if]);
        $ah_a = Str::padLeft(dechex($a_a), 2, '0');
        $ah_b = Str::padLeft(dechex($a_b), 2, '0');
        $ah_c = Str::padLeft(dechex($a_c), 2, '0');
        $ah_d = Str::padLeft(dechex($a_d), 2, '0');
        $ah_e = Str::padLeft(dechex($a_e), 2, '0');
        $ah_f = Str::padLeft(dechex($a_f), 2, '0');
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
