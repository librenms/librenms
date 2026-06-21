<?php

$SPoids = SnmpQuery::enumStrings()->hideMib()->walk('SPAGENT-MIB::temperatureSensorTable')->valuesByIndex();
$SXoids = SnmpQuery::enumStrings()->hideMib()->walk('SPAGENT-MIB::temperatureTable')->valuesByIndex();

// Old SensorProbe devices
foreach ($SPoids as $entry) {
    if ($entry['temperatureSensorGoOffline'] == 'online' || $entry['temperatureSensorGoOffline'] == '1') {
        $clean_index = trim((string) $entry['temperatureSensorIndex'], '"');

        $scale = $entry['temperatureSensorUnit'] == 'F' ? 'fahrenheit' : $entry['temperatureSensorUnit'];

        if (isset($entry['temperatureSensorRaw'])) {
            $oid = '.1.3.6.1.4.1.3854.2.5.2.1.20.' . $clean_index;
            $divisor = 10;
            $value = $entry['temperatureSensorRaw'] / $divisor;
        } else {
            $oid = '.1.3.6.1.4.1.3854.2.5.2.1.4.' . $clean_index;
            $divisor = 1;
            $value = $entry['temperatureSensorDegree'];
        }

        discover_sensor(
            null,
            'temperature',
            $device,
            $oid,
            $clean_index,
            'akcp',
            $entry['temperatureSensorDescription'],
            $divisor,
            1,
            fahrenheit_to_celsius($entry['temperatureSensorLowCritical'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureSensorLowWarning'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureSensorHighWarning'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureSensorHighCritical'] / $divisor, $scale),
            fahrenheit_to_celsius($value, $scale),
            'snmp',
            null,
            null,
            $scale == 'fahrenheit' ? 'fahrenheit_to_celsius' : null
        );
    }
}

// Newer SPx Devices
foreach ($SXoids as $entry) {
    if ($entry['temperatureGoOffline'] == 'online' || $entry['temperatureGoOffline'] == '1') {
        $clean_index = trim((string) $entry['temperatureIndex'], '"');

        $scale = $entry['temperatureUnit'] == 'F' ? 'fahrenheit' : $entry['temperatureUnit'];

        if (isset($entry['temperatureRaw'])) {
            $oid = '.1.3.6.1.4.1.3854.3.5.2.1.20.' . $clean_index;
            $divisor = 10;
            $value = $entry['temperatureRaw'] / $divisor;
        } else {
            $oid = '.1.3.6.1.4.1.3854.3.5.2.1.4.' . $clean_index;
            $divisor = 1;
            $value = $entry['temperatureDegree'];
        }

        discover_sensor(
            null,
            'temperature',
            $device,
            $oid,
            $clean_index,
            'akcp',
            $entry['temperatureDescription'],
            $divisor,
            1,
            fahrenheit_to_celsius($entry['temperatureLowCritical'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureLowWarning'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureHighWarning'] / $divisor, $scale),
            fahrenheit_to_celsius($entry['temperatureHighCritical'] / $divisor, $scale),
            fahrenheit_to_celsius($value, $scale),
            'snmp',
            null,
            null,
            $scale == 'fahrenheit' ? 'fahrenheit_to_celsius' : null
        );
    }
}
