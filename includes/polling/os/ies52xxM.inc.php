<?php

# iesSeries (IES42XXM, IES52XXM, IES62XXM)
if (strpos($device['sysObjectID'], '.1.3.6.1.4.1.890.1.5.13')!==false) {
    $hardware = trim(snmp_get($device, 'sysProductDescr.0', '-OQv', 'IES5206-MIB'), '"');
    $serial = trim(snmp_get($device, 'sysSerialNumber.0', '-OQv', 'IES5206-MIB'), '"');
    $version = trim(snmp_get($device, 'sysBootupFwVersion.0', '-OQv', 'IES5206-MIB'), '"');
}
