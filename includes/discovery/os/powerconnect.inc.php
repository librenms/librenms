<?php

if (!$os) {
    if (stristr($sysDescr, 'PowerConnect ') && !stristr($sysDescr, 'ArubaOS')) {
        $os = 'powerconnect';
    } elseif (preg_match('/Dell.*Gigabit\ Ethernet/i', $sysDescr)) {
        $os = 'powerconnect';
    } elseif (strstr($sysDescr, 'Neyland 24T')) {
        /* PowerConnect 5324 */
        $os = 'powerconnect';
    } elseif (stristr(snmp_get($device, '1.3.6.1.4.1.674.10895.3000.1.2.100.1.0', '-Oqv', ''), 'PowerConnect')) {
        $os = 'powerconnect';
    }
}
