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

$ceragon_type = snmp_get($device, 'sysObjectID.0', '-mSNMPv2-MIB -Oqv', '');
$hardware = rewrite_ceraos_hardware($ceragon_type); // function in ./includes/rewrites.php
$version = snmp_get($device, 'genEquipMngSwIDUVersionsRunningVersion.1', '-mMWRM-RADIO-MIB -Oqv', '');
$serial = snmp_walk($device, 'genEquipInventorySerialNumber', '-mMWRM-RADIO-MIB -Oqv', '');
$latitude = snmp_walk($device, 'genEquipUnitLatitude', '-mMWRM-RADIO-MIB -Oqv', '');
$longitude = snmp_walk($device, 'genEquipUnitLongitude', '-mMWRM-RADIO-MIB -Oqv', '');

$IfIndex = 0;
$ifIndexesArray = array();
$ifIndexesArray = explode("\n", snmp_walk($device, "ifIndex", "-mIF-MIB -Oqv", ""));
$num_radios = 0;

foreach ($ifIndexesArray as $IfIndex) {
    $IfDescr = snmp_get($device, "ifDescr.$IfIndex", "-mIF-MIB -Oqv", "");
    $IfName = snmp_get($device, "ifName.$IfIndex", "-mIF-MIB -Oqv", "");
    if (stristr($IfDescr, "Radio")) {
        $num_radios = $num_radios+1;
    }
}
$features = $num_radios . " radios in unit";
