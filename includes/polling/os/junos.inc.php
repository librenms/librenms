<?php

use LibreNMS\RRD\RrdDefinition;

$oid_list = 'jnxJsSPUMonitoringCurrentFlowSession.0';
$srx_sess_data = snmp_get_multi($device, $oid_list, '-OUQs', 'JUNIPER-SRX5000-SPU-MONITORING-MIB');

if (is_numeric($srx_sess_data[0]['jnxJsSPUMonitoringCurrentFlowSession'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('spu_flow_sessions', 'GAUGE', 0),
    );
    $fields = array(
        'spu_flow_sessions' => $srx_sess_data[0]['jnxJsSPUMonitoringCurrentFlowSession'],
    );

    data_update($device, 'junos_jsrx_spu_sessions', $tags, $fields);

    $graphs['junos_jsrx_spu_sessions'] = true;
    echo ' Flow Sessions';
    unset($srx_sess_data);
}

$version = snmp_get($device, 'jnxVirtualChassisMemberSWVersion.0', '-Oqv', 'JUNIPER-VIRTUALCHASSIS-MIB');
if (empty($version)) {
    preg_match('/kernel JUNOS (\S+),/', $device['sysDescr'], $jun_ver);
    $version = $jun_ver[1];
}
if (empty($version)) {
    preg_match('/\[(.+)\]/', snmp_get($device, '.1.3.6.1.2.1.25.6.3.1.2.2', '-Oqv', 'HOST-RESOURCES-MIB'), $jun_ver);
    $version = $jun_ver[1];
}

if (strpos($device['sysDescr'], 'olive')) {
    $hardware = 'Olive';
    $serial   = '';
} else {
    $boxDescr = snmp_get($device, 'jnxBoxDescr.0', '-Oqv', 'JUNIPER-MIB');
    if (!empty($boxDescr) && $boxDescr != "Juniper Virtual Chassis Switch") {
        $hardware = $boxDescr;
    } else {
        $hardware = snmp_translate($device['sysObjectID'], 'Juniper-Products-MIB:JUNIPER-CHASSIS-DEFINES-MIB', 'junos');
        $hardware = 'Juniper '.rewrite_junos_hardware($hardware);
    }
    $serial   = snmp_get($device, '.1.3.6.1.4.1.2636.3.1.3.0', '-OQv', '+JUNIPER-MIB', 'junos');
}

$features       = '';
