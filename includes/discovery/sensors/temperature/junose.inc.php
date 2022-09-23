<?php

echo 'JunOSe: ';
$oids = snmpwalk_cache_multi_oid($device, 'juniSystemTempValue', [], 'Juniper-System-MIB', 'junose');
if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (is_numeric($entry['juniSystemTempValue']) && is_numeric($index) && $entry['juniSystemTempValue'] > '0') {
            $entPhysicalIndex = snmp_get($device, 'juniSystemTempPhysicalIndex.' . $index, '-Oqv', 'Juniper-System-MIB', 'junose');
            $descr = snmp_get($device, 'entPhysicalDescr.' . $entPhysicalIndex, '-Oqv', 'ENTITY-MIB');
            $descr = preg_replace('/^Juniper\ [0-9a-zA-Z\-]+/', '', $descr);
            // Wipe out ugly Juniper crap. Why put vendor and model in here? Idiots!
            $descr = str_replace('temperature sensor on', '', trim($descr));
            $oid = '.1.3.6.1.4.1.4874.2.2.2.1.9.4.1.3.' . $index;
            $current = $entry['juniSystemTempValue'];

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'junose', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}
