<?php

echo "Toshiba Tec Printer \n";

# alternative node HOST-RESOURCES-MIB::hrDeviceDescr.1 (contains model, serial and firmware on table printers)
if(preg_match('/Toshiba\s+([\w\-]+)/i', snmp_get($device, "sysDescr.0", "-OQv"), $DESCR_PARTS) )
{
    $hardware = $DESCR_PARTS[1];
}

if(preg_match('/([\w\d]+)\-?\d*\-?\d*/i', snmp_get($device, "SNMPv2-SMI::mib-2.43.5.1.1.17.1", "-OQv"), $PARTS))
{
    $serial = $PARTS[1];
}

unset($DESCR_PARTS, $PARTS);
