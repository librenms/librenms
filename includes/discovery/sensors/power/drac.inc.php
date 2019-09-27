<?php

$power_index =  snmp_walk($device, 'amperageProbeIndex.1', '-Oqv', 'IDRAC-MIB');
$divisor = 1;
$multiplier =1;

if ($power_index) {
    d_echo('iDRAC power');

    foreach (explode("\n", $power_index) as $index) {
        $sensor_type = snmp_get($device, "amperageProbeType.1.".$index, '-Ovq', 'IDRAC-MIB');
        if ($sensor_type == "amperageProbeTypeIsSystemWatts") {
            $sensor_reading = snmp_get($device, "amperageProbeReading.1.".$index, '-Ovq', 'IDRAC-MIB');
            $sensor_description = snmp_get($device, "amperageProbeLocationName.1.".$index, '-Ovq', 'IDRAC-MIB');
            $sensor_oid = ".1.3.6.1.4.1.674.10892.5.4.600.30.1.6.1.".$index;
            if ($sensor_reading >= 0) {
                discover_sensor($valid['sensor'], 'power', $device, $sensor_oid, $index, 'drac', $sensor_description, $divisor, $multiplier, null, null, null, null, $sensor_reading);
            }
        }
    }
}
