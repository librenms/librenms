<?php

if ($device['os'] == 'alphabridgev1switch') {
    $sysDescr = snmp_get($device, '1.3.6.1.2.1.1.1.0', '-Oqv');

    if (strpos($sysDescr, '439') !== false) {
        discover_processor($valid['processor'], $device, 1, 'alphabridgev1switch-439', $sysDescr, 1, $sysDescr);
    } elseif (strpos($sysDescr, '315') !== false) {
        discover_processor($valid['processor'], $device, 1, 'alphabridgev1switch-315', $sysDescr, 1, $sysDescr);
    }
}
