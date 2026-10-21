<?php

// LTE modem state columns are free-form strings, map them to the numeric states created at discovery.
// Keep the previous state when the oid is missing from the response (e.g. one bulk chunk timed out),
// so an SNMP timeout does not look like a missing SIM. An empty ICCID in the response is a missing SIM.
if (isset(\LibreNMS\OS\Routeros::LTE_STATES[$sensor['sensor_type']])) {
    $sensor_value = isset($snmp_data[$sensor['sensor_oid']])
        ? \LibreNMS\OS\Routeros::normalizeLteState($sensor['sensor_type'], $sensor_value)
        : $sensor['sensor_current'];
}

// The sensor oid is the installed RouterBOOT version, compare it with the version RouterOS would upgrade to.
// Keep the previous state when either version can't be fetched, so a lost packet does not change the state.
if ($sensor['sensor_type'] == 'mtxrFirmwareUpgrade') {
    $upgrade = $sensor_value === '' ? '' : (string) SnmpQuery::get('MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0')->value();
    $sensor_value = $sensor_value === '' || $upgrade === ''
        ? $sensor['sensor_current']
        : \LibreNMS\OS\Routeros::firmwareState($sensor_value, $upgrade);
}
