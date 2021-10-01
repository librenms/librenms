<?php

$divisor = '100';
$outlet_divisor = $divisor;
$multiplier = '1';

// These PDUs may have > 1 "tower" accessible via a single management interface
$tower_count = snmp_get($device, 'systemTowerCount.0', '-Ovq', 'Sentry3-MIB');
$towers = 1;
while ($towers <= $tower_count) {
    // Check for Infeeds
    $infeed_oids = snmp_walk($device, "infeedID.$towers", '-Osqn', 'Sentry3-MIB');
    d_echo($infeed_oids . "\n");

    $infeed_oids = trim($infeed_oids);

    if ($infeed_oids) {
        echo 'ServerTech Sentry Infeed ';
    }

    foreach (explode("\n", $infeed_oids) as $infeed_data) {
        $infeed_data = trim($infeed_data);
        if ($infeed_data) {
            [$infeed_oid,$descr] = explode(' ', $infeed_data, 2);
            $split_oid = explode('.', $infeed_oid);
            $infeed_index = $split_oid[(count($split_oid) - 1)];

            // infeedLoadValue
            $infeed_oid = '.1.3.6.1.4.1.1718.3.2.2.1.7.' . $towers . '.' . $infeed_index;

            $descr_string = snmp_get($device, "infeedID.$towers.$infeed_index", '-Ovq', 'Sentry3-MIB');
            $descr = "Infeed $descr_string";
            $low_warn_limit = null;
            $low_limit = null;
            $high_warn_limit = snmp_get($device, "infeedLoadHighThresh.$towers.$infeed_index", '-Ovq', 'Sentry3-MIB');
            $high_limit = snmp_get($device, "infeedCapacity.$towers.$infeed_index", '-Ovq', 'Sentry3-MIB');
            $current = (snmp_get($device, "$infeed_oid", '-Ovq', 'Sentry3-MIB') / $divisor);

            if ($current >= 0) {
                $tmp_index = "infeedID.$towers.$infeed_index";
                discover_sensor($valid['sensor'], 'current', $device, $infeed_oid, $tmp_index, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
            }

            // Check for per-outlet polling
            // $outlet_oids = snmp_walk($device, "outletLoadValue.$towers.$infeed_index", "-Osqn", "Sentry3-MIB");
            $outlet_oids = snmp_walk($device, "outletLoadValue.$towers.1", '-Osqn', 'Sentry3-MIB');
            $outlet_oids = trim($outlet_oids);

            if ($outlet_oids) {
                echo 'ServerTech Sentry Outlet ';
            }

            foreach (explode("\n", $outlet_oids) as $outlet_data) {
                $outlet_data = trim($outlet_data);
                if ($outlet_data) {
                    [$outlet_oid,$outlet_descr] = explode(' ', $outlet_data, 2);
                    $outlet_split_oid = explode('.', $outlet_oid);
                    $outlet_index = $outlet_split_oid[(count($outlet_split_oid) - 1)];

                    $outletsuffix = "$towers.$infeed_index.$outlet_index";

                    // outletLoadValue: "A non-negative value indicates the measured load in hundredths of Amps"
                    $outlet_oid = ".1.3.6.1.4.1.1718.3.2.3.1.7.$outletsuffix";
                    $outlet_descr_string = snmp_get($device, "outletID.$outletsuffix", '-Ovq', 'Sentry3-MIB');
                    $outlet_descr = "Outlet $outlet_descr_string";
                    $outlet_low_warn_limit = null;
                    $outlet_low_limit = null;
                    $outlet_high_warn_limit = null;
                    // Should be "outletCapacity" but is always -1. According to MIB: "A negative value indicates that the capacity was not available."
                    $outlet_high_limit = snmp_get($device, "outletLoadHighThresh.$outletsuffix", '-Ovq', 'Sentry3-MIB');
                    $outlet_current = (snmp_get($device, "$outlet_oid", '-Ovq', 'Sentry3-MIB') / $outlet_divisor);

                    if ($outlet_current >= 0) {
                        $outlet_insert_index = "outletID.$towers.$infeed_index.$outlet_index";
                        discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'sentry3', $outlet_descr, $outlet_divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);
                    }
                } //end if

                unset($outlet_data);
                unset($outlet_oids);
                unset($outlet_oid);
                unset($outlet_descr_string);
                unset($outlet_descr);
                unset($outlet_low_warn_limit);
                unset($outlet_low_limit);
                unset($outlet_high_warn_limit);
                unset($outlet_high_limit);
                unset($outlet_current);
            } //end foreach
        } //end if
        unset($infeed_data);
        unset($infeed_oids);
        unset($descr_string);
        unset($descr);
        unset($low_warn_limit);
        unset($low_limit);
        unset($high_warn_limit);
        unset($high_limit);
        unset($current);
    } //end foreach

    $towers++;
} //end while

unset($towers);
