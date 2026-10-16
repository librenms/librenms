<?php

// LTE modem state columns are free-form strings, map them to the numeric states created at discovery.
// Leave the value alone when the whole request failed so an SNMP timeout does not look like a missing SIM.
if (isset(\LibreNMS\OS\Routeros::LTE_STATES[$sensor['sensor_type']]) && ! empty($snmp_data)) {
    $sensor_value = \LibreNMS\OS\Routeros::normalizeLteState($sensor['sensor_type'], $sensor_value);
}

// The sensor oid is the installed RouterBOOT version, compare it with the version RouterOS would upgrade to
if ($sensor['sensor_type'] == 'mtxrFirmwareUpgrade' && $sensor_value !== '') {
    $sensor_value = \LibreNMS\OS\Routeros::firmwareState(
        $sensor_value,
        SnmpQuery::get('MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0')->value()
    );
}
