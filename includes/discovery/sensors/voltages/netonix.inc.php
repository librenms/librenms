<?php

// Netonix Voltages
if ($device['os'] == 'netonix') {
    echo 'Netonix: ';
    $oids = snmpwalk_cache_multi_oid($device, 'voltageTable', array(), 'NETONIX-SWITCH-MIB', '+'.$config['mibdir'].'/netonix');
    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            if (is_numeric($entry['voltage']) && is_numeric($index) && $entry['voltage'] > '0') {
                $descr   = $entry['voltageDescription'];
                $oid     = '.1.3.6.1.4.1.46242.4.1.3.'.$index;
                $current = $entry['voltage'];
                $divisor = 100;
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $device['os'], $descr, $divisor, '1', null, null, null, null, $current);
            }
        }
    }
}//end if
