<?php

if ($device['os'] == 'avaya-ers') {
    // Get major version number of running firmware
    $fw_major_version = null;
    preg_match('/[0-9]\.[0-9]/', $device['version'], $fw_major_version);
    $fw_major_version = $fw_major_version[0];

    // Temperature info only known to be present in firmware 6.1 or higher
    if ($fw_major_version >= 6.1) {
        $temps = snmp_walk($device, '1.3.6.1.4.1.45.1.6.3.7.1.1.5.5', '-Osqn');

        foreach (explode("\n", $temps) as $i => $t) {
            $t   = explode(' ', $t);
            $oid = $t[0];
            $val = $t[1];
            // Sensors are reported as 2 * value
            $val = (trim($val) / 2);
            discover_sensor($valid['sensor'], 'temperature', $device,
                $oid, zeropad($i + 1), 'avaya-ers',
                'Unit '.($i + 1).' temperature', '2', '1', null, null, null, null, $val);
        }
    }
}
