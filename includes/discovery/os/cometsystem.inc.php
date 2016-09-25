<?php

if (preg_match('/.+Firmware Version \d+-\d+-\d+.+/', $sysDescr)) {
    if (is_numeric(snmp_get($device, '.1.3.6.1.4.1.22626.1.5.2.1.3.0', '-Oqv', ''))) {
        $os = 'cometsystem-p85xx';
    }
}
