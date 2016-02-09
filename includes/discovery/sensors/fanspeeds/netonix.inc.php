<?php

// Netonix Fan Speeds
if ($device['os'] == 'netonix') {
    echo 'Netonix: ';
    $oids = snmpwalk_cache_multi_oid($device, 'fanTable', array(), 'NETONIX-SWITCH-MIB', '+'.$config['mibdir'].'/netonix');
    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            if (is_numeric($entry['fanSpeed']) && is_numeric($index)) {
                $descr   = $index;
                $oid     = '.1.3.6.1.4.1.46242.2.1.2.'.$index;
                $current = $entry['fanSpeed'];
                discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, $device['os'], $descr, '1', '1', null, null, null, null, $current);
            }
        }
    }
}//end if
