# Sensor State Support

## Introduction

This section will explain how to implement support for sensor state. 
It will also cover the basic concepts of sensor state monitoring.

LibreNMS simplifies sensor state monitoring by translating raw values 
into understandable generic states like "OK", "Warning", "Critical", and 
"Unknown", enabling consistent visualization and easier analysis.

## Key Concepts

For sensor state monitoring, we have 4 DB tables we need to concentrate about. 
These tables act as a bridge between the raw information provided by each sensor 
and the standardized representation (generic state) that LibreNMS uses 
for visualization and alert generation.

### Table: sensors

*Each time a sensor needs to be polled, the system needs to know which
sensor (regardless of its type) is it that it needs to poll and its description, 
at what oid is this sensor located, what class the sensor is, etc.*

### Table: sensors_to_state_indexes

*Is as you might have guessed, where the sensor_id is mapped 
to a state_index_id.*

### Table: state_indexes

*Is where we keep track of the state information we monitor.*

### Table: state_translations

*Is where we map the possible returned state sensor values to a
generic LibreNMS value, in order to make displaying and alerting more
generic. We also map these values to the actual state
sensor(state_index) where these values are actually returned from.*

*The LibreNMS generic states are derived from Nagios:*

```
0 = OK
1 = Warning
2 = Critical
3 = Unknown
```

 ### Generic States translations

LibreNMS offers flexibility in handling sensor states, which can be represented 
as either strings or numbers via SNMP. 

If the sensor state input is a string (i.e. "ONLINE") 
librenms will use the 'descr' field and finally translate it to the desired 
generic state (0, 1, 2 or 3)
- { value: 4, **descr: online**, graph: 1, **generic: 0** }

If the sensor state input is a number (i.e. "4" representing the offline state) 
librenms will use the 'value' field and finally translate it to the desired 
generic state (0, 1, 2 or 3).  
- { **value: 0**, descr: offline, graph: 1, **generic: 2** }

!!! note
    Here the descr field is used as a label to visualize the value on screen, 
    but not as an input to translate to a generic state because the state input
    is a number.

## YAML Example

For YAML based state discovery:

```yaml
mib: NETBOTZV2-MIB
modules:
    sensors:
        state:
            data:
                -
                    oid: dryContactSensorTable
                    value: dryContactSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.1.1.2.{{ $index }}'
                    descr: dryContactSensorLabel
                    group: Contact Sensors
                    index: 'dryContactSensor.{{ $index }}'
                    state_name: dryContactSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: open }
                        - { value:  1, generic: 2, graph: 0, descr: closed }
                -
                    oid: doorSwitchSensorTable
                    value: doorSwitchSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.2.1.2.{{ $index }}'
                    descr: doorSwitchSensorLabel
                    group: Switch Sensors
                    index: 'doorSwitchSensor.{{ $index }}'
                    state_name: doorSwitchSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: open }
                        - { value:  1, generic: 2, graph: 0, descr: closed }
                -
                    oid: cameraMotionSensorTable
                    value: cameraMotionSensorValue
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.3.1.2.{{ $index }}'
                    descr: cameraMotionSensorLabel
                    group: Camera Motion Sensors
                    index: 'cameraMotionSensor.{{ $index }}'
                    state_name: cameraMotionSensor
                    states:
                        - { value: -1, generic: 3, graph: 0, descr: 'null' }
                        - { value:  0, generic: 0, graph: 0, descr: noMotion }
                        - { value:  1, generic: 2, graph: 0, descr: motionDetected }
                -
                    oid: otherStateSensorTable
                    value: otherStateSensorErrorStatus
                    num_oid: '.1.3.6.1.4.1.5528.100.4.2.10.1.3.{{ $index }}'
                    descr: otherStateSensorLabel
                    index: '{{ $index }}'
                    state_name: otherStateSensorErrorStatus
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

This example will be based on a Cisco power supply sensor and is all
it takes to have sensor state support for Cisco power supplies in Cisco
switches. The file should be located in 
/includes/discovery/sensors/state/cisco.inc.php.

```php
<?php

$oids = snmpwalk_group($device, 'ciscoEnvMonSupplyStatusTable', 'CISCO-ENVMON-MIB');

if (!empty($oids)) {
    //Create State Index
    $state_name = 'ciscoEnvMonSupplyState';
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
