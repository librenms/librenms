<?php

/*
 *
 * @link       https://www.librenms.org
 *
 * @author     peca.nesovanovic <peca.nesovanovic@sattrakt.com>
 */

echo 'RFC1628 ';

// Battery Status (Value : 1 unknown, 2 batteryNormal, 3 batteryLow, 4 batteryDepleted)
$state = SnmpQuery::get('UPS-MIB::upsBatteryStatus.0')->value();
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsBatteryStatusState';
    create_state_index(
        $state_name,
        [
            ['value' => 1, 'generic' => 3, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 0, 'descr' => 'Normal'],
            ['value' => 3, 'generic' => 2, 'descr' => 'Low'],
            ['value' => 4, 'generic' => 2, 'descr' => 'Depleted'],
        ]
    );

    $sensor_index = 0;
    discover_sensor(
        null,
        'state',
        $device,
        '.1.3.6.1.2.1.33.1.2.1.0',
        $sensor_index,
        $state_name,
        'Battery Status',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );
}

// Output Source (Value : 1 other, 2 none, 3 normal, 4 bypass, 5 battery, 6 booster, 7 reducer)
$state = SnmpQuery::get('UPS-MIB::upsOutputSource.0')->value();
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsOutputSourceState';
    create_state_index(
        $state_name,
        [
            ['value' => 1, 'generic' => 3, 'descr' => 'Other'],
            ['value' => 2, 'generic' => 3, 'descr' => 'None'],
            ['value' => 3, 'generic' => 0, 'descr' => 'Normal'],
            ['value' => 4, 'generic' => 1, 'descr' => 'Bypass'],
            ['value' => 5, 'generic' => 2, 'descr' => 'Battery'],
            ['value' => 6, 'generic' => 2, 'descr' => 'Booster'],
            ['value' => 7, 'generic' => 2, 'descr' => 'Reducer'],
        ]
    );

    $sensor_index = 0;
    discover_sensor(
        null,
        'state',
        $device,
        '.1.3.6.1.2.1.33.1.4.1.0',
        $sensor_index,
        $state_name,
        'Output Source',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );
}

// UPS battery test status
$state = SnmpQuery::get('UPS-MIB::upsTestResultsSummary.0')->value();
if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upsTestResult';
    create_state_index(
        $state_name,
        [
            ['value' => 1, 'generic' => 0, 'descr' => 'OK'],
            ['value' => 2, 'generic' => 1, 'descr' => 'Warning'],
            ['value' => 3, 'generic' => 2, 'descr' => 'Error'],
            ['value' => 4, 'generic' => 1, 'descr' => 'Aborted'],
            ['value' => 5, 'generic' => 1, 'descr' => 'inProgress'],
            ['value' => 6, 'generic' => 3, 'descr' => 'noTestInitiated'],
        ]
    );

    $sensor_index = 0;
    discover_sensor(
        null,
        'state',
        $device,
        '.1.3.6.1.2.1.33.1.7.3.0',
        $sensor_index,
        $state_name,
        'UPS Test',
        1,
        1,
        null,
        null,
        null,
        null,
        $state,
        'snmp',
        0
    );
}
