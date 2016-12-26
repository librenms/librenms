<?php

// APC
if ($device['os'] == 'apc') {
    echo 'APC ';
    $oids = snmpwalk_cache_oid($device, 'atsInputFrequency', array(), 'PowerNet-MIB');
    foreach ($oids as $index => $data) {
        $type = 'apcInFeed';
        $divisor = 1;
        $oid = '.1.3.6.1.4.1.318.1.1.8.5.3.2.1.4.' . $index;
        $current = $data['atsInputFrequency'];
        $descr = 'Input Feed';
        if (count($oids) > 1) {
            $descr .= ' ' . chr(64 + $index);
        }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }

    $oids = snmpwalk_cache_oid($device, 'atsOutputFrequency', array(), 'PowerNet-MIB');
    foreach ($oids as $index => $data) {
        $type = 'apcOutFeed';
        $divisor = 1;
        $oid = '.1.3.6.1.4.1.318.1.1.8.5.4.2.1.4.' . $index;
        $current = $data['atsOutputFrequency'];
        $descr = 'Output Feed';
        if (count($oids) > 1) {
            $descr .= ' ' . chr(64 + $index);
        }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }

    $oids = snmpwalk_cache_oid($device, 'upsAdvInputFrequency', array(), 'PowerNet-MIB');
    foreach ($oids as $index => $data) {
        $type = 'apcIn';
        $divisor = 1;
        $descr = 'Input';
        $oid = '.1.3.6.1.4.1.318.1.1.1.3.2.4.' . $index;
        $current = $data['upsAdvInputFrequency'];

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }

    $oids = snmpwalk_cache_oid($device, 'upsAdvOutputFrequency', array(), 'PowerNet-MIB');
    foreach ($oids as $index => $data) {
        $type = 'apcOut';
        $divisor = 1;
        $descr = 'Output';
        $oid = '.1.3.6.1.4.1.318.1.1.1.4.2.2.' . $index;
        $current = $data['upsAdvOutputFrequency'];

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }
}//end if
