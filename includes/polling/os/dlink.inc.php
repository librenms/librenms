<?php

$Descr_string  = $device['sysDescr'];
$Descr_chopper = preg_split('/[ ]+/', "$Descr_string");

$hardware = $Descr_chopper[0].' Rev. '.str_replace('"', '', snmp_get($device, '.1.3.6.1.2.1.16.19.3.0', '-Oqv'));

$versionOIDList = [
     'DLINKSW-ENTITY-EXT-MIB::dEntityExtVersionRuntime.1',
     '.1.3.6.1.2.1.16.19.2.0',
     '.1.3.6.1.4.1.171.12.11.1.9.4.1.11.1',
];

foreach ($versionOIDList as $oid) {
        $version_tmp = snmp_get($device, $oid, '-Oqv');
     
    if (!empty($version_tmp)) {
        $version = $version_tmp;
        break;
    }
}

$serialOIDList = [
     '.1.3.6.1.4.1.171.12.11.1.9.4.1.17.1',
     '.1.3.6.1.4.1.171.12.1.1.12.0',
];

foreach ($serialOIDList as $oid) {
        $serial_tmp = snmp_get($device, $oid, '-Oqv');
    if (!empty($serial_tmp)) {
        $serial = $serial_tmp;
        break;
    }
}
