<?php

// Check Inlets
$inlet_oids = snmp_walk($device, 'inletLabel', '-Osqn', 'PDU2-MIB');
$inlet_oids = trim($inlet_oids);
if ($inlet_oids) {
    echo 'PDU Inlet ';
}

foreach (explode("\n", $inlet_oids) as $inlet_data) {
    $inlet_data = trim($inlet_data);
    if ($inlet_data) {
        [$inlet_oid,$inlet_descr] = explode(' ', $inlet_data, 2);
        $inlet_split_oid = explode('.', $inlet_oid);
        $inlet_index = $inlet_split_oid[(count($inlet_split_oid) - 2)] . '.' . $inlet_split_oid[(count($inlet_split_oid) - 1)];

        $inlet_oid = ".1.3.6.1.4.1.13742.6.5.2.3.1.4.$inlet_index.23";
        $inlet_divisor = pow(10, snmp_get($device, "inletSensorDecimalDigits.$inlet_index.frequency", '-Ovq', 'PDU2-MIB'));
        $inlet_frequency = snmp_get($device, "measurementsInletSensorValue.$inlet_index.frequency", '-Ovq', 'PDU2-MIB');
        if (is_numeric($inlet_frequency)) {
            $inlet_frequency = ($inlet_frequency / $inlet_divisor);
            discover_sensor($valid['sensor'], 'frequency', $device, $inlet_oid, $inlet_index, 'raritan', $inlet_descr, $inlet_divisor, 1, null, null, null, null, $inlet_frequency);
        }
    }
}
