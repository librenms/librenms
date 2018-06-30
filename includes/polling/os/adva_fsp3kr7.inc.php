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

// **********  FSP3000 R7 Devices
$version  = trim(snmp_get($device, "swVersionActiveApplSw.100737280", "-OQv", "ADVA-MIB"), '"');
$hardware = trim(snmp_get($device, "inventoryUnitName.33619968", "-OQv", "ADVA-MIB"), '"')
            .' V'.trim(snmp_get($device, "inventoryHardwareRev.33619968", "-OQv", "ADVA-MIB"), '"');
$serial = trim(snmp_get($device, "inventorySerialNum.33619968", "-OQv", "ADVA-MIB"), '"');
