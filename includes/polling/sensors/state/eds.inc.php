<?php

//Workaround for bad behaviour of the SNMP engine in EDS device.
//a ".0" is added in snmpget compared to snmpwalk of the same table.
if (! array_key_exists($sensor['sensor_oid'], $snmp_data) && array_key_exists($sensor['sensor_oid'] . '.0', $snmp_data)) {
    $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid'] . '.0']));
}
