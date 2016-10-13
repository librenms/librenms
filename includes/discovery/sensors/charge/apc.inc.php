<?php

if ($device['os'] == 'apc') {
    $oids = snmpwalk_cache_oid($device, 'upsHighPrecBatteryCapacity', array(), 'PowerNet-MIB');
    if (empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'upsAdvBatteryCapacity', $oids, 'PowerNet-MIB');
    }

    foreach ($oids as $index => $data) {
        $type = 'apc';
        $descr = 'Battery Charge';
        $limit = 100;
        $lowlimit = 0;
        $warnlimit = 10;

        if (isset($data['upsHighPrecBatteryCapacity'])) {
            $divisor = 10;
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.2.3.1.' . $index;
            $current = $data['upsHighPrecBatteryCapacity'] / $divisor;
        } else {
            $divisor = 1;
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.2.2.1.' . $index;
            $current = $data['upsAdvBatteryCapacity'];
        }

        discover_sensor($valid['sensor'], 'charge', $device, $current_oid, $index, $type, $descr, $divisor, 1, $lowlimit, $warnlimit, null, $limit, $current);
    }
}//end if
