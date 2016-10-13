<?php

// APC
if ($device['os'] == 'apc') {
    echo 'APC ';
    // UPS
    $oids = snmpwalk_cache_oid($device, 'upsHighPrecOutputLoad', array(), 'PowerNet-MIB');
    if (empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'upsAdvOutputLoad', $oids, 'PowerNet-MIB');
    }

    foreach ($oids as $index => $data) {
        $type = 'apc';
        $descr = 'Load';

        if (isset($data['upsHighPrecOutputLoad'])) {
            $divisor = 10;
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3.' . $index;
            $current = $data['upsHighPrecOutputLoad'] / $divisor;
        } else {
            $divisor = 1;
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3.' . $index;
            $current = $data['upsAdvOutputLoad'];
        }

        discover_sensor($valid['sensor'], 'load', $device, $current_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }
}//end if
