<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$ceragon_type = $poll_device['sysObjectID'];
$hardware = rewrite_ceraos_hardware($ceragon_type); // function in ./includes/rewrites.php
if (stristr('IP10', $hardware)) {
    $serial = snmp_get($device, 'genEquipUnitIDUSerialNumber.0', '-mMWRM-RADIO-MIB -Oqv', '');
} else {
    $serial = snmp_get($device, 'genEquipInventorySerialNumber.127', '-mMWRM-RADIO-MIB -Oqv', '');
}
$multi_get_array = snmp_get_multi($device, 'genEquipMngSwIDUVersionsRunningVersion.1 genEquipUnitLatitude.0 genEquipUnitLongitude.0', '-OQU', 'MWRM-RADIO-MIB', 'mibs/');
d_echo($multi_get_array);
$version = $multi_get_array[1]['MWRM-UNIT-MIB::genEquipMngSwIDUVersionsRunningVersion'];
$latitude = $multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLatitude'];
$longitude = $multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLongitude'];
echo "\n.\n";

$ifIndex_array = array();
$ifIndex_array = explode("\n", snmp_walk($device, 'ifIndex', '-Oqv', 'IF-MIB', 'mibs/'));
d_echo($ifIndex_array);
$snmp_get_oids = "";
foreach ($ifIndex_array as $ifIndex) {
    $snmp_get_oids .= "ifDescr.$ifIndex ";
}
echo "\n.\n";

$num_radios = 0;
//$ifDescr_array = array();
$ifDescr_array = snmp_get_multi($device, $snmp_get_oids, '-OQU', 'IF-MIB', 'mibs/');
print_r($ifDescr_array);
d_echo("\$ifDescr_array = " . $ifDescr_array);
foreach ($ifIndex_array as $ifIndex) {
    d_echo("\$ifDescr_array[\$ifIndex]['IF-MIB::ifDescr'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifDescr']) . "\n";
    if (stristr($ifDescr_array[$ifIndex]['IF-MIB::ifDescr'], "Radio")) {
        $num_radios = $num_radios+1;
    }
}
$features = $num_radios . " radios in unit";
echo "\n.\n";

unset($ceragon_type, $multi_get_array, $ifIndexesArray, $ifIndex, $ifDescr_array, $ifDescr, $num_radios);
