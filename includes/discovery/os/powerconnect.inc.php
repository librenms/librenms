<?php

if (str_contains($sysDescr, 'PowerConnect') && !str_contains($sysDescr, 'ArubaOS')) {
    $os = 'powerconnect';
}

if (str_contains($sysDescr, 'Neyland 24T')) {
    $os = 'powerconnect';
}

if (str_contains($sysDescr, 'Dell', true) && str_contains($sysDescr, 'Gigabit Ethernet', true)) {
    $os = 'powerconnect';
}

if (str_contains(snmp_get($device, '1.3.6.1.4.1.674.10895.3000.1.2.100.1.0', '-Oqv', ''), 'PowerConnect')) {
    $os = 'powerconnect';
}
