<?php

$divisor = 1;
if (strstr($device['sysDescr'], 'Cambium PTP 50650')) {
    $mib = 'CAMBIUM-PTP650-MIB';
    $oid = '.1.3.6.1.4.1.17713.7.12.2.0';
    $divisor = 10;
} elseif (strstr($device['sysDescr'], 'PTP250')) {
    $oid = '.1.3.6.1.4.1.17713.250.5.1.0';
    $mib = 'CAMBIUM-PTP250-MIB';
    $divisor = 10;
} elseif (strstr($device['sysObjectID'], '.17713.21')) {
    $epmp_ap = snmp_get($device, 'wirelessInterfaceMode.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    $epmp_number = snmp_get($device, 'cambiumSubModeType.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    if ($epmp_ap == 1) {
        if ($epmp_number != 1) {
            $oid = '.1.3.6.1.4.1.17713.21.1.2.3.0';
            $mib = 'CAMBIUM-PMP80211-MIB';
        }
    } else {
        $oid = '.1.3.6.1.4.1.17713.21.1.2.3.0';
        $mib = 'CAMBIUM-PMP80211-MIB';
    }
} else {
    $oid = '.1.3.6.1.4.1.161.19.3.2.2.21.0';
    $mib = 'WHISP-BOX-MIBV2-MIB';
}

$oids = trim(str_replace('"', '', snmp_get($device, "$oid", '-OsqnU', $mib)));
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'Cambium Signal ';
}

$type = 'cambium';
if (! empty($oids)) {
    [,$current] = explode(' ', $oids);
    $current = $current / $divisor;
    $index = $oid;
    $descr = 'Signal';
    discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
