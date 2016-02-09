<?php

// Netonix Temperatures
if ($device['os'] == 'netonix') {
    echo 'Netonix: ';
    $oids = snmpwalk_cache_multi_oid($device, 'tempTable', array(), 'NETONIX-SWITCH-MIB', '+'.$config['mibdir'].'/netonix');
    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            if (is_numeric($entry['temp']) && is_numeric($index) && $entry['temp'] > '0') {
                $descr   = $entry['tempDescription'];
                $oid     = '.1.3.6.1.4.1.46242.3.1.3.'.$index;
                $current = $entry['temp'];
                discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $device['os'], $descr, '1', '1', null, null, null, null, $current);
            }
        }
    }
}//end if
