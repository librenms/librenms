<?php

$oids = snmpwalk_cache_oid($device, 'sensorProbeTempTable', [], 'SPAGENT-MIB');
d_echo($oids . "\n");

foreach ($oids as $index => $entry) {
    if ($entry['sensorProbeTempOnline'] == 'online') {
        $scale = $entry['sensorProbeTempDegreeType'] == 'fahr' ? 'fahrenheit' : $entry['sensorProbeTempDegreeType'];

        if (isset($entry['sensorProbeTempDegreeRaw'])) {
            $oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.14.$index";
            $divisor = 10;
            $value = $entry['sensorProbeTempDegreeRaw'] / $divisor;
        } else {
            $oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.3.$index";
            $divisor = 1;
            $value = $entry['sensorProbeTempDegree'];
        }

        discover_sensor(
            $valid['sensor'],
            'temperature',
            $device,
            $oid,
            $index,
            'akcp',
            $entry['sensorProbeTempDescription'],
            $divisor,
            1,
            fahrenheit_to_celsius($entry['sensorProbeTempLowCritical'], $scale),
            fahrenheit_to_celsius($entry['sensorProbeTempLowWarning'], $scale),
            fahrenheit_to_celsius($entry['sensorProbeTempHighWarning'], $scale),
            fahrenheit_to_celsius($entry['sensorProbeTempHighCritical'], $scale),
            fahrenheit_to_celsius($value, $scale),
            'snmp',
            null,
            null,
            $scale == 'fahrenheit' ? 'fahrenheit_to_celsius' : null
        );
    }
}
