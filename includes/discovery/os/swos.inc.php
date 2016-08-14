<?php

if (!$os) {
    if (is_numeric(snmp_get($device, 'SNMPv2-SMI::enterprises.14988.2', '-Oqv', ''))) {
        $os = 'swos';
    }
}
