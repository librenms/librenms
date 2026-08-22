# Sensor State Support

## Introduction

This section describes the implementation of sensor state support. It
also describes the basic concepts of sensor state monitoring.

LibreNMS converts the raw values to generic states: "OK", "Warning",
"Critical", and "Unknown". These states give a consistent display and a
simpler analysis.

## Key Concepts

Sensor state monitoring uses 4 database tables. These tables connect
the raw information of each sensor to the standard representation, that
is the generic state. LibreNMS uses the generic state for the display
and for the alerts.

### Table: sensors

*This table gives the sensor of each poll. It holds the description of
the sensor, its OID, and its class. It applies to every sensor type.*

### Table: sensors_to_state_indexes

*This table maps each sensor_id to a state_index_id.*

### Table: state_indexes

*This table holds the state information of the monitoring.*

### Table: state_translations

*This table maps each state sensor value to a generic LibreNMS value.
The display and the alerts are then generic. It also maps each value to
the state sensor (state_index) of that value.*

*The LibreNMS generic states come from Nagios:*

```
0 = OK
1 = Warning
2 = Critical
3 = Unknown
```

 ### Generic States translations

A sensor state arrives over SNMP as a string or as a number. LibreNMS
handles both forms. 

If the sensor state input is a string, such as "ONLINE", LibreNMS uses
the 'descr' field. It then converts the value to the generic state 0,
1, 2, or 3:
- { value: 4, **descr: online**, graph: 1, **generic: 0** }

If the sensor state input is a number, such as "4" for the offline
state, LibreNMS uses the 'value' field. It then converts the value to
the generic state 0, 1, 2, or 3:  
- { **value: 0**, descr: offline, graph: 1, **generic: 2** }

!!! note
    Here, the descr field is only a label on the screen. It is not the
    input of the conversion, because the state input is a number.

## YAML Example

For YAML based state discovery:

```yaml
modules:
    sensors:
        state:
            data:
                -
                    oid: NETBOTZV2-MIB::dryContactSensorTable
                    value: NETBOTZV2-MIB::dryContactSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.1.1.2.{{ $index }}'
                    descr: NETBOTZV2-MIB::dryContactSensorLabel
                    group: Contact Sensors
                    index: 'dryContactSensor.{{ $index }}'
                    state_name: NETBOTZV2-MIB::dryContactSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: open }
                        - { value:  1, generic: 2, graph: 0, descr: closed }
                -
                    oid: NETBOTZV2-MIB::doorSwitchSensorTable
                    value: NETBOTZV2-MIB::doorSwitchSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.2.1.2.{{ $index }}'
                    descr: NETBOTZV2-MIB::doorSwitchSensorLabel
                    group: Switch Sensors
                    index: 'doorSwitchSensor.{{ $index }}'
                    state_name: NETBOTZV2-MIB::doorSwitchSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: open }
                        - { value:  1, generic: 2, graph: 0, descr: closed }
                -
                    oid: NETBOTZV2-MIB::cameraMotionSensorTable
                    value: NETBOTZV2-MIB::cameraMotionSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.3.1.2.{{ $index }}'
                    descr: NETBOTZV2-MIB::cameraMotionSensorLabel
                    group: Camera Motion Sensors
                    index: 'cameraMotionSensor.{{ $index }}'
                    state_name: NETBOTZV2-MIB::cameraMotionSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: noMotion }
                        - { value:  1, generic: 2, graph: 0, descr: motionDetected }
                -
                    oid: NETBOTZV2-MIB::otherStateSensorTable
                    value: NETBOTZV2-MIB::otherStateSensorErrorStatus
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.10.1.3.{{ $index }}'
                    descr: NETBOTZV2-MIB::otherStateSensorLabel
                    index: '{{ $index }}'
                    state_name: NETBOTZV2-MIB::otherStateSensorErrorStatus
                    states:
                        - { value: 0, generic: 0, graph: 0, descr: normal }
                        - { value: 1, generic: 1, graph: 0, descr: info }
                        - { value: 2, generic: 1, graph: 0, descr: warning }
                        - { value: 3, generic: 2, graph: 0, descr: error }
                        - { value: 4, generic: 2, graph: 0, descr: critical }
                        - { value: 5, generic: 2, graph: 0, descr: failure }
```

## Advanced Example

For advanced state discovery:

This example uses a Cisco power supply sensor. It gives full sensor
state support for the power supplies of Cisco switches. Put the file in
`/includes/discovery/sensors/state/cisco.inc.php`.

```php
<?php

$oids = SnmpQuery::hideMib()->walk('CISCO-ENVMON-MIB::ciscoEnvMonSupplyStatusTable')->valuesByIndex;

if (!empty($oids)) {
    //Create State Index
    $state_name = 'CISCO-ENVMON-MIB::ciscoEnvMonSupplyState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
        ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'shutdown'],
        ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
        ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'notFunctioning'],
    ];
    create_state_index($state_name, $states);

    $num_oid = '.1.3.6.1.4.1.9.9.13.1.5.1.3.';
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor(null, 'state', $device, $num_oid.$index, $index, $state_name, $entry['ciscoEnvMonSupplyStatusDescr'], '1', '1', null, null, null, null, $entry['ciscoEnvMonSupplyState'], 'snmp', $index);
    }
}
```
