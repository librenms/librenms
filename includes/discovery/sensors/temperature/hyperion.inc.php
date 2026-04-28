<?php
/**
 * LibreNMS sensor discovery for Hyperion switch
 */

$ifdescr = snmpwalk_cache_oid($device, 'ifDescr', [], 'IF-MIB');

$ddmi_table = snmpwalk_cache_oid(
    $device,
    'ddmiStatusInterfaceA2CurrentTemperature',
    [],
    'MIB-DDMI'
);

$ddmi_ifindex = snmpwalk_cache_oid(
    $device,
    'ddmiStatusInterfaceIfIndex',
    [],
    'MIB-DDMI'
);

foreach ($ddmi_table as $index => $entry) {
    $value = $entry['ddmiStatusInterfaceA2CurrentTemperature'] ?? null;

    if (!is_numeric($value)) {
        continue;
    }

    $if_val       = $ddmi_ifindex[$index]['ddmiStatusInterfaceIfIndex'] ?? $index;
    $descr_index  = (int)$if_val + 1000000;

    $port_name = $ifdescr[$descr_index]['ifDescr'] ?? "Optic Port $if_val";

    $oid_num      = '.1.3.6.1.4.1.19829.1.121.1.3.2.1.1003.' . $index;
    $sensor_index = 'ddmiStatusInterfaceA2CurrentTemperature.' . $index;

    discover_sensor(
        null,                        // $pre_cache
        'temperature',               // $class
        $device,                     // $device
        $oid_num,                    // $oid (numeryczny)
        $sensor_index,               // $index (unikalny)
        'hyperion',                  // $type (nazwa OS)
        $port_name,                  // $descr → np. "10GigabitEthernet 1/1"
        1,                           // $divisor
        1,                           // $multiplier
        null,                        // $low_limit
        null,                        // $low_warn_limit
        null,                        // $warn_limit
        null,                        // $high_limit
        $value,                      // $current
        'snmp',                      // $poller_type
        null,                        // $entPhysicalIndex
        null,                        // $entPhysicalIndex_measured
        null,                        // $user_func
        'Optic Module Temperature'   // $group
    );
}

$thermal_base_oid = '.1.3.6.1.4.1.19829.1.119.1.3.1.1.1';

$thermal_table = snmpwalk_cache_oid(
    $device,
    'thermalProtectionStatusInterfaceTemperature',
    [],
    'MIB-THERMAL-PROTECTION'
);

foreach ($thermal_table as $index => $entry) {
    $value = $entry['thermalProtectionStatusInterfaceTemperature'] ?? null;

    if (!is_numeric($value)) {
        continue;
    }


    $port_name = $ifdescr[$index]['ifDescr'] ?? "Port $index";

    $oid_num      = '.1.3.6.1.4.1.19829.1.78.1.3.1.1.2.' . $index;
    $sensor_index = 'thermalProtectionStatusInterfaceTemperature.' . $index;

    discover_sensor(
        null,
        'temperature',
        $device,
        $oid_num,
        $sensor_index,
        'hyperion',
        $port_name,
        1,
        1,
        null,
        null,
        null,
        null,
        $value,
        'snmp',
        null,
        null,
        null,
        'Interface Temperature'
    );
}
