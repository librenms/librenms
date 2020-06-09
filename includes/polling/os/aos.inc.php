<?php
if (strpos($device['sysObjectID'], '1.3.6.1.4.1.6486.800.1.1.2.2.4')) {
    $hardware = snmp_get($device, '.1.3.6.1.4.1.89.53.4.1.6.1', '-Osqv', 'RADLAN-Physicaldescription-MIB'); //RADLAN-Physicaldescription-MIB::rlPhdStackProductID
    $version = snmp_get($device, '.1.3.6.1.4.1.89.53.14.1.2.1', '-Osqv', 'RADLAN-Physicaldescription-MIB'); //RADLAN-Physicaldescription-MIB::rlPhdUnitGenParamSoftwareVersion
} else {
    list(,$hardware,$version) = explode(' ', $device['sysDescr']);
}
