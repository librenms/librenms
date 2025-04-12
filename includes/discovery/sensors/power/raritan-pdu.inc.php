<?php

$divisor = '1';
$multiplier = '1';

// Check PDU MIB Outlets
$outlet_oids = snmp_walk($device, 'outletIndex', '-Osqn', 'PDU-MIB');
$outlet_oids = trim($outlet_oids);
if ($outlet_oids) {
    d_echo('PDU MIB Outlet');
    foreach (explode("\n", $outlet_oids) as $outlet_data) {
        $outlet_data = trim($outlet_data);
        if ($outlet_data) {
            [$outlet_oid,$outlet_descr] = explode(' ', $outlet_data, 2);
            $outlet_split_oid = explode('.', $outlet_oid);
            $outlet_index = $outlet_split_oid[count($outlet_split_oid) - 1];
            $outletsuffix = "$outlet_index";
            $outlet_insert_index = $outlet_index;
            $outlet_oid = ".1.3.6.1.4.1.13742.4.1.2.2.1.8.$outletsuffix";
            $outlet_descr = snmp_get($device, "outletLabel.$outletsuffix", '-Ovq', 'PDU-MIB');
            $outlet_power = snmp_get($device, "outletApparentPower.$outletsuffix", '-Ovq', 'PDU-MIB');
            if ($outlet_power >= 0) {
                discover_sensor(null, 'power', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $divisor, $multiplier, null, null, null, null, $outlet_power);
            }
        }
    }
}
