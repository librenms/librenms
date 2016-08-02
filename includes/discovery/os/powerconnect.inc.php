<?php

if (!$os) {
    // if (strstr($sysDescr, "Neyland 24T")) { $os = "powerconnect"; } /* Powerconnect 5324 */
    if (stristr($sysDescr, 'PowerConnect ') && !stristr($sysDescr, 'ArubaOS')) {
        $os = 'powerconnect';
    }
    else if (preg_match('/Dell.*Gigabit\ Ethernet/i', $sysDescr)) {
        $os = 'powerconnect';
    } //end if
    else if (stristr(snmp_get($device, '1.3.6.1.4.1.674.10895.3000.1.2.100.1.0', '-Oqv', ''), 'PowerConnect')) {
        $os = 'powerconnect';
    }
}
