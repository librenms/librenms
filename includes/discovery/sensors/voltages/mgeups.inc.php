<?php

// MGE UPS Voltages
if ($device['os'] == 'mgeups') {
    echo 'MGE ';
    $oids = trim(snmp_walk($device, 'mgoutputVoltage', '-OsqnU', 'MG-SNMP-UPS-MIB'));
    d_echo($oids."\n");

    $numPhase = count(explode("\n", $oids));
    for ($i = 1; $i <= $numPhase; $i++) {
        $volt_oid  = ".1.3.6.1.4.1.705.1.7.2.1.2.$i";
        $descr = 'Output';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $volt_oid, '-Oqv');
        if (!$current) {
            $volt_oid .= '.0';
            $current   = snmp_get($device, $volt_oid, '-Oqv');
        }

        $current /= 10;
        $type     = 'mge-ups';
        $divisor  = 10;
        $index    = $i;

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $oids = trim(snmp_walk($device, 'mgeinputVoltage', '-OsqnU', 'MG-SNMP-UPS-MIB'));
    d_echo($oids."\n");

    $numPhase = count(explode("\n", $oids));
    for ($i = 1; $i <= $numPhase; $i++) {
        $volt_oid = ".1.3.6.1.4.1.705.1.6.2.1.2.$i";
        $descr    = 'Input';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $volt_oid, '-Oqv');
        if (!$current) {
            $volt_oid .= '.0';
            $current   = snmp_get($device, $volt_oid, '-Oqv');
        }

        $current /= 10;
        $type     = 'mge-ups';
        $divisor  = 10;
        $index    = (100 + $i);

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $numPhase = count(explode("\n", $oids));
    for ($i = 1; $i <= $numPhase; $i++) {
        $load_oid = ".1.3.6.1.4.1.705.1.7.2.1.4.$i";
        $descr    = 'Output Load';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $load_oid, '-Oqv');
        if (!$current) {
            $load_oid .= '.0';
            $Phaseload   = snmp_get($device, $load_oid, '-Oqv');
        }

//        $current /= 10;
        $type     = 'mge-ups';
//        $divisor  = 10;
        $index    = (100 + $i);

        discover_sensor($valid['sensor'], 'load', $device, $load_oid, $index, $type, $descr, '1', '1', null, null, null, null, $load);
    }

    $ups_battery_voltage_oid = '.1.3.6.1.4.1.705.1.5.5.0';
    $ups_battery_voltage     = snmp_get($device, $ups_battery_voltage_oid, '-Oqv');

		$ups_battery_voltage /= 10;
        $type     = 'Battery Voltage';
		$divisor  = 10;
        $index    = (100 + $i);	
   
   if (is_numeric($ups_battery_voltage)) {
        discover_sensor($valid['sensor'], 'voltage', $device, $ups_battery_voltage_oid, $index, $type, 'UPS Battery Voltage', $divisor, '1', null, null, null, null, $ups_battery_voltage);
   }

    $ups_battery_level_oid = '.1.3.6.1.4.1.705.1.5.2.0';
    $ups_battery_level     = snmp_get($device, $ups_battery_level_oid, '-Oqv');

    if (is_numeric($ups_battery_level)) {
        discover_sensor($valid['sensor'], 'charge', $device, $ups_battery_level_oid, 'UPS Battery Level', $ups_device_manufacturer.' '.$ups_device_model, 'UPS Battery Level', '1', '1', null, null, null, null, $ups_battery_level);
    }

//    $ups_battery_remaining_time_oid = '.1.3.6.1.4.1.705.1.5.1.0';
//    $ups_battery_remaining_time     = snmp_get($device, $ups_battery_remaining_time_oid, '-Oqv');
//
//		$ups_battery_remaining_time /= 60;
//        $type     = 'mge-ups';
//        $divisor  = 60;
//        $index    = (100 + $i);	
	
//    if (is_numeric($ups_battery_remaining_time)) {
//        discover_sensor($valid['sensor'], 'time', $device, $ups_battery_remaining_time_oid, $index, $type, 'UPS Battery Remaining Time', $divisor, '1', null, null, null, null, $ups_battery_remaining_time);
//    }

//    $ups_battery_level_oid = '.1.3.6.1.4.1.705.1.5.2.0';
//    $ups_battery_level     = snmp_get($device, $ups_battery_level_oid, '-Oqv');
//
//    if (is_numeric($ups_battery_level)) {
//        discover_sensor($valid['sensor'], 'charge', $device, $ups_battery_level_oid, 'UPS Battery Level', $ups_device_manufacturer.' '.$ups_device_model, 'UPS Battery Level', '1', '1', null, null, null, null, $ups_battery_level);
//    }

}//end if
