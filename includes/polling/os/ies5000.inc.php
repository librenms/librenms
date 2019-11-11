<?php
# ies 5106
if ($device['sysObjectID'] == '.1.3.6.1.4.1.890.1.5.13.10') {
    $r_temp = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.890.1.5.13.5.6.2.1.3.0', '.1.3.6.1.4.1.8886.6.1.1.1.2.0', '.1.3.6.1.4.1.8886.6.1.1.1.14.0'], '-OQUn');
    $serial   = $r_temp['.1.3.6.1.4.1.890.1.5.13.5.6.2.1.3.0'];

    #$hardware = trim(snmp_get($device, 'sysProductDescr.0', '-OQv', 'IES5206-MIB'), '"');
    #$serial = trim(snmp_get($device, 'chassisSerialNumber.0', '-LE 3 -Pu -OQv', 'ZYXEL-IES5000-MIB'), '"');
    #$version = trim(snmp_get($device, 'sysBootupFwVersion.0', '-OQv', 'IES5206-MIB'), '"');
}
