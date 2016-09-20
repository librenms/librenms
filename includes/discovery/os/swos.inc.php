<?php

if (str_contains($sysDescr, array('RB260GS', 'RB250GS', 'RB260GSP'))) {
    if (str_contains(snmp_get($device, 'SNMPv2-MIB::sysName.0', '-Oqv', ''), 'MicroTik')) {
        $os = 'swos';
    }
}
