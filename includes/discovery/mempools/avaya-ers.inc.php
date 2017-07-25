<?php

$OID = '.1.3.6.1.4.1.45.1.6.3.8.1.1.12';

if ($device['os'] == 'avaya-ers') {
    // Memory information only known to work with 3500, 5500 and 5600 switches
    if (preg_match('/[35][56][0-9][0-9]/', $device['sysDescr'])) {
        // Get major version number of running firmware
        $fw_major_version = null;
        preg_match('/[0-9]\.[0-9]/', $device['version'], $fw_major_version);
        $fw_major_version = $fw_major_version[0];

        // Temperature info only known to be present in firmware 6.1 or higher
        // Also present on firmware 5.1 or higher on 3500 switches
        if ($fw_major_version >= 5.1) {
            $mem = snmp_walk($device, $OID, '-Osqn');

            echo "$mem\n";

            foreach (explode("\n", $mem) as $i => $t) {
                $t   = explode(' ', $t);
                $oid = str_replace($OID, '', $t[0]);
                discover_mempool($valid_mempool, $device, $oid, 'avaya-ers', 'Unit '.($i + 1).' memory', '1', null, null);
            }
        }
    }
}
