<?php
if (empty($entity_array)) {
    $entity_array = array();
    echo ' entPhysicalDescr';
    $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entPhysicalName';
    $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'CISCO-ENTITY-SENSOR-MIB');
}


$state_data = snmpwalk_group($device, 'entStateTable', 'ENTITY-STATE-MIB');

// define types of entity state sensors
$types = array(
    'entStateOper' => array(
        'oid' => '.1.3.6.1.2.1.131.1.1.1.3',
        'states' => array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unavailable'),
            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'disabled'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'enabled'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'testing'),
        ),
    ),
    'entStateUsage' => array(
        'oid' => '.1.3.6.1.2.1.131.1.1.1.4',
        'states' => array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unavailable'),
            array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'idle'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'active'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'busy'),
        ),
    ),
//    'entStateAlarm' => array(
//        'oid' => '.1.3.6.1.2.1.131.1.1.1.5',
//        'states' => array(
//            array('value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unavailable'),
//            array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'underRepair'),
//            array('value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'),
//            array('value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'major'),
//            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'minor'),
//            array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'),
//            array('value' => 6, 'generic' => 3, 'graph' => 0, 'descr' => 'indeterminate'),
//        ),
//    ),
    'entStateStandby' => array(
        'oid' => '.1.3.6.1.2.1.131.1.1.1.6',
        'states' => array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unavailable'),
            array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'hotStandby'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'coldStandby'),
            array('value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'providingService'),
        ),
    ),
);

foreach ($state_data as $index => $data) {
    foreach ($types as $type_name => $type_data) {
        if (isset($data[$type_name])) {
            $current = $data[$type_name];
            extract($type_data);
            /** @var string $type_name */
            /** @var string $oid */
            /** @var array $states */

            $descr = $entity_array[$index]['entPhysicalName'];
            $full_oid = $oid . '.' . $index;

            create_state_index($type_name, $states);
            discover_sensor(
                $valid_sensor,
                'state',
                $device,
                $full_oid,
                $index,
                $type_name,
                $descr,
                1,
                1,
                null,
                null,
                null,
                null,
                $current,
                'snmp',
                $index
            );
            create_sensor_to_state_index($device, $type_name, $index);
        }
    }
}

global $debug;
if ($debug) {
    var_dump($state_data,  $entity_array);
    exit;
}
