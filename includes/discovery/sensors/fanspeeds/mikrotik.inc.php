<?php

if ($device['os'] == 'routeros') {
    $descr_prefix = 'fan ';
    $oids = array(
        '.1.3.6.1.4.1.14988.1.1.3.17.0', // MIKROTIK-MIB::mtxrHlFanSpeed1.0
        '.1.3.6.1.4.1.14988.1.1.3.18.0', // MIKROTIK-MIB::mtxrHlFanSpeed2.0
    );

    echo 'MIKROTIK-MIB ';
    foreach ($oids as $index => $oid) {
        $value = trim(snmp_get($device, $oid, '-Oqv'), '"');

        if (is_numeric($value)) {
            $descr = $descr_prefix . ($index + 1);
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'snmp', $descr, 1, 1, null, null, null, null, $value);
        }
    }
}
