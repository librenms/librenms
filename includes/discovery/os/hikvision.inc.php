<?php

if (str_contains(snmp_get($device, '.1.3.6.1.4.1.39165.1.6.0', '-Oqv', ''), 'Hikvision')) {
    $os = 'hikvision';
} elseif (starts_with($sysDescr, 'Hikvision company products')) {
    $os = 'hikvision';
}
