<?php
// =========================================================================
// SENSOR 2: MIB-DDMI
// =========================================================================
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
    $value_raw = $entry['ddmiStatusInterfaceA2CurrentTemperature'] ?? null;
    if ($value_raw === null) {
        continue;
    }
    $value = preg_replace('/[^0-9.\-]/', '', $value_raw);
    if ($value === '' || !is_numeric($value)) {
        continue;
    }
    $value = (float)$value;
    $if_val       = $ddmi_ifindex[$index]['ddmiStatusInterfaceIfIndex'] ?? $index;
    $descr_index  = (int)$if_val + 1000000;
    $port_name = $ifdescr[$descr_index]['ifDescr'] ?? "Optic Port $if_val";
    $oid_num      = '.1.3.6.1.4.1.19829.1.121.1.3.2.1.1003.' . $index;
    $sensor_index = 'ddmiStatusInterfaceA2CurrentTemperature.' . $index;
    d_echo("DDMI SENSOR: $oid_num = $value\n");
    discover_sensor(
        null,
        'temperature',
        $device,
        $oid_num,
        $sensor_index,
        'hyperion-ddmi',
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
        'Optic Module Temperature'
    );
}
// =========================================================================
// SENSOR 2: MIB-THERMAL-PROTECTION
// =========================================================================
$thermal_base_oid = '.1.3.6.1.4.1.19829.1.78.1.3.1.1.2.';
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
        'hyperion-thermal',
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