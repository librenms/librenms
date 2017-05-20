<?php
/**
 * LibreNMS - ADVA FSP150 device support
 *
 * @category Network_Monitoring
 * @package  LibreNMS
 * @author   Christoph Zilian <czilian@hotmail.com>
 * @license  http://gnu.org/copyleft/gpl.html GNU GPL
 * @link     https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// ***********  FSP150 Devices
if (starts_with($device['sysObjectID'], 'enterprises.2544.1.12.1.1')) {
    $version  = 'FSP150 SW V'.trim(snmp_get($device, "entPhysicalSoftwareRev.1", "-OQv", "ADVA-MIB"), '"');
    $hardware = 'ADVA '.trim(snmp_get($device, "entPhysicalName.1", "-OQv", "ADVA-MIB"), '"')
                .' V'.trim(snmp_get($device, "entPhysicalHardwareRev.1", "-OQv", "ADVA-MIB"), '"');
    $serial = trim(snmp_get($device, "entPhysicalSerialNum.1", "-OQv", "ADVA-MIB"), '"');
    $features = ''; //search for PTP info in MIB
}// End If FSP150 Devices


// **********  FSP3000 R7 Devices
if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {
    $version  = 'FSP3000R7 SW V'.trim(snmp_get($device, "swVersionActiveApplSw.100737280", "-OQv", "ADVA-MIB"), '"');
    $hardware = 'ADVA FSP3000R7 '.trim(snmp_get($device, "inventoryUnitName.33619968", "-OQv", "ADVA-MIB"), '"')
                .' V'.trim(snmp_get($device, "inventoryHardwareRev.33619968", "-OQv", "ADVA-MIB"), '"');
    $serial = trim(snmp_get($device, "inventorySerialNum.33619968", "-OQv", "ADVA-MIB"), '"');
}// *********  End FSP3000 Devices
