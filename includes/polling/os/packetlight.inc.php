<?php

$pl_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.4515.1.3.6.1.1.1.5.0', '.1.3.6.1.4.1.4515.1.3.6.1.1.1.7.0', '.1.3.6.1.4.1.4515.1.3.6.1.1.1.2.0', '.1.3.6.1.4.1.4515.1.3.6.1.1.1.4.0']);

d_echo($pl_data);

$version       = $pl_data['.1.3.6.1.4.1.4515.1.3.6.1.1.1.5.0']; //SL-ENTITY-MIB::slEntPhysicalFirmwareRev
$serial        = $pl_data['.1.3.6.1.4.1.4515.1.3.6.1.1.1.7.0']; //SL-ENTITY-MIB::slEntPhysicalSerialNum
$hardware      = $pl_data['.1.3.6.1.4.1.4515.1.3.6.1.1.1.2.0']; //SL-ENTITY-MIB::slEntPhysicalDescr
if ($pl_data['.1.3.6.1.4.1.4515.1.3.6.1.1.1.4.0']) {
    // SL-ENTITY-MIB::slEntPhysicalHardwareRev
    $hardware .= ' rev ' . $pl_data['.1.3.6.1.4.1.4515.1.3.6.1.1.1.4.0'];
}
