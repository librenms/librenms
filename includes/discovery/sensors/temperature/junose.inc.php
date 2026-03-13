<?php

echo 'JunOSe: ';
$oids = snmpwalk_cache_multi_oid($device, 'juniSystemTempValue', [], 'Juniper-System-MIB', 'juniper/junose');
if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (is_numeric($entry['juniSystemTempValue']) && is_numeric($index) && $entry['juniSystemTempValue'] > '0') {
            $entPhysicalIndex = SnmpQuery::mibDir('juniper/junose')->get('Juniper-System-MIB::juniSystemTempPhysicalIndex.' . $index)->value();
            $descr = SnmpQuery::get('ENTITY-MIB::entPhysicalDescr.' . $entPhysicalIndex)->value();
            $descr = preg_replace('/^Juniper\ [0-9a-zA-Z\-]+/', '', (string) $descr);
            // Wipe out ugly Juniper crap. Why put vendor and model in here? Idiots!
            $descr = str_replace('temperature sensor on', '', trim((string) $descr));
            $oid = '.1.3.6.1.4.1.4874.2.2.2.1.9.4.1.3.' . $index;
            $current = $entry['juniSystemTempValue'];

            discover_sensor(null, \LibreNMS\Enum\Sensor::Temperature, $device, $oid, $index, 'junose', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}
