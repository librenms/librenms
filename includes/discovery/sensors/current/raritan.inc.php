<?php

// blindly copied from sentry3
if ($device['os'] == 'raritan') {
    $divisor        = '1000';
    $outlet_divisor = $divisor;
    $multiplier     = '1';

    // Check Inlets
    $inlet_oids = snmp_walk($device, 'inletLabel', '-Osqn', 'PDU2-MIB');
    $inlet_oids = trim($inlet_oids);
    if ($inlet_oids) {
        echo 'PDU Inlet ';
    }

    foreach (explode("\n", $inlet_oids) as $inlet_data) {
        $inlet_data = trim($inlet_data);
        if ($inlet_data) {
            list($inlet_oid,$inlet_descr) = explode(' ', $inlet_data, 2);
            $inlet_split_oid              = explode('.', $inlet_oid);
            $inlet_index                  = $inlet_split_oid[(count($inlet_split_oid) - 2)].'.'.$inlet_split_oid[(count($inlet_split_oid) - 1)];

            $inlet_oid     = ".1.3.6.1.4.1.13742.6.5.2.3.1.4.$inlet_index.1";
            $inlet_divisor = pow(10, snmp_get($device, "inletSensorDecimalDigits.$inlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB'));
            $inlet_current = (snmp_get($device, "measurementsInletSensorValue.$inlet_index.1", '-Ovq', 'PDU2-MIB') / $inlet_divisor);

            if ($inlet_current >= 0) {
                discover_sensor($valid['sensor'], 'current', $device, $inlet_oid, $inlet_index, 'raritan', $inlet_descr, $inlet_divisor, $multiplier, null, null, null, null, $inlet_current);
            }
        }
    }


    //
    // Check for per-outlet polling
    $outlet_oids = snmp_walk($device, 'outletIndex', '-Osqn', 'PDU-MIB');
    $outlet_oids = trim($outlet_oids);

    if ($outlet_oids) {
        echo 'PDU Outlet ';
    }

    foreach (explode("\n", $outlet_oids) as $outlet_data) {
        $outlet_data = trim($outlet_data);
        if ($outlet_data) {
            list($outlet_oid,$outlet_descr) = explode(' ', $outlet_data, 2);
            $outlet_split_oid               = explode('.', $outlet_oid);
            $outlet_index                   = $outlet_split_oid[(count($outlet_split_oid) - 1)];

            $outletsuffix        = "$outlet_index";
            $outlet_insert_index = $outlet_index;

            // outletLoadValue: "A non-negative value indicates the measured load in milli Amps"
            $outlet_oid             = ".1.3.6.1.4.1.13742.4.1.2.2.1.4.$outletsuffix";
            $outlet_descr           = snmp_get($device, "outletLabel.$outletsuffix", '-Ovq', 'PDU-MIB');
            $outlet_low_warn_limit  = null;
            $outlet_low_limit       = null;
            $outlet_high_warn_limit = (snmp_get($device, "outletCurrentUpperWarning.$outletsuffix", '-Ovq', 'PDU-MIB') / $outlet_divisor);
            $outlet_high_limit      = (snmp_get($device, "outletCurrentUpperCritical.$outletsuffix", '-Ovq', 'PDU-MIB') / $outlet_divisor);
            $outlet_current         = (snmp_get($device, "outletCurrent.$outletsuffix", '-Ovq', 'PDU-MIB') / $outlet_divisor);

            if ($outlet_current >= 0) {
                discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $outlet_divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);
            }
        }


        // unset($outlet_data);
        // unset($outlet_oids);
        // unset($outlet_oid);
        // unset($outlet_descr);
        // unset($outlet_low_warn_limit);
        // unset($outlet_low_limit);
        // unset($outlet_high_warn_limit);
        // unset($outlet_high_limit);
        // unset($outlet_current);
    }
}
