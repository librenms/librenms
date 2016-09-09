<?php

if (!$os) {
    if (strstr(snmp_get($device, 'SNMPv2-MIB::sysName.0', '-Oqv', ''), 'MikroTik')) {
        if (strstr(snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Oqv', ''), 'RB260GS')) {
            $os = 'swos';
        }
    }
}
