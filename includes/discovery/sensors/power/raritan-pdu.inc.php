<?php

$divisor = '1';
$multiplier = '1';

// Check PDU2 MIB Inlets
$inlet_oids = snmp_walk($device, 'inletLabel', '-Osqn', 'PDU2-MIB');
$inlet_oids = trim($inlet_oids);
if ($inlet_oids) {
    d_echo('PDU2 MIB Inlet');
    foreach (explode("\n", $inlet_oids) as $inlet_data) {
        $inlet_data = trim($inlet_data);
        if ($inlet_data) {
            [$inlet_oid,$inlet_descr] = explode(' ', $inlet_data, 2);
            $inlet_split_oid = explode('.', $inlet_oid);
            $inlet_index = $inlet_split_oid[(count($inlet_split_oid) - 2)] . '.' . $inlet_split_oid[(count($inlet_split_oid) - 1)];
            $inlet_oid = ".1.3.6.1.4.1.13742.6.5.2.3.1.4.$inlet_index.5";
            $inlet_divisor = pow(10, snmp_get($device, "inletSensorDecimalDigits.$inlet_index.activePower", '-Ovq', 'PDU2-MIB'));
            $inlet_power = (snmp_get($device, "measurementsInletSensorValue.$inlet_index.activePower", '-Ovq', 'PDU2-MIB') / $inlet_divisor);
            if ($inlet_power >= 0) {
                discover_sensor($valid['sensor'], 'power', $device, $inlet_oid, $inlet_index, 'raritan', $inlet_descr, $inlet_divisor, $multiplier, null, null, null, null, $inlet_power);
            }
        }
    }
}

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
            $outlet_index = $outlet_split_oid[(count($outlet_split_oid) - 1)];
            $outletsuffix = "$outlet_index";
            $outlet_insert_index = $outlet_index;
            $outlet_oid = ".1.3.6.1.4.1.13742.4.1.2.2.1.8.$outletsuffix";
            $outlet_descr = snmp_get($device, "outletLabel.$outletsuffix", '-Ovq', 'PDU-MIB');
            $outlet_power = (snmp_get($device, "outletApparentPower.$outletsuffix", '-Ovq', 'PDU-MIB'));
            if ($outlet_power >= 0) {
                discover_sensor($valid['sensor'], 'power', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $divisor, $multiplier, null, null, null, null, $outlet_power);
            }
        }
    }
}

// Check PDU2 MIB Outlets
$outlet_oids = snmp_walk($device, 'outletLabel', '-Osqn', 'PDU2-MIB');
$outlet_oids = trim($outlet_oids);
if ($outlet_oids) {
    d_echo('PDU2 MIB Outlet');
    foreach (explode("\n", $outlet_oids) as $outlet_data) {
        $outlet_data = trim($outlet_data);
        if ($outlet_data) {
            [$outlet_oid,$outlet_descr] = explode(' ', $outlet_data, 2);
            $outlet_split_oid = explode('.', $outlet_oid);
            $outlet_index = $outlet_split_oid[(count($outlet_split_oid) - 1)];
            $outletsuffix = "$outlet_index";
            $outlet_insert_index = $outlet_index;
            $outlet_oid = ".1.3.6.1.4.1.13742.6.5.4.3.1.4.1.$outletsuffix.5";
            $outlet_descr = snmp_get($device, "outletName.1.$outletsuffix", '-Ovq', 'PDU2-MIB');
            if (! $outlet_descr) {
                $outlet_descr = 'Outlet ' . $outletsuffix;
            }
            $outlet_divisor = pow(10, snmp_get($device, "outletSensorDecimalDigits.1.$outlet_index.activePower", '-Ovq', 'PDU2-MIB'));
            $outlet_power = (snmp_get($device, "measurementsOutletSensorValue.1.$outlet_index.activePower", '-Ovq', 'PDU2-MIB') / $outlet_divisor);
            if ($outlet_power >= 0) {
                discover_sensor($valid['sensor'], 'power', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $outlet_divisor, $multiplier, null, null, null, null, $outlet_power);
            }
        }
    }
}
