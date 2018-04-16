<?php
/**
 * LibreNMS - FiberHome Switch device support - OS module
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage Fiber Home Switch device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

function hexbin($hex_string) // convert the hex OID content to character
{
    $chr_string = '';
    foreach (explode(' ', $hex_string) as $a) {
        $chr_string .= chr(hexdec($a));
    }
    return $chr_string;
}

$sysDescrPieces = explode(" ", snmp_get($device, 'sysDescr.0', '-Oqv', 'SNMPv2-MIB'));   //extract model from sysDescr

$hwversion = str_replace(array("\r","\n"), '', snmp_get($device, 'msppDevHwVersion.0', '-Oqv', 'WRI-DEVICE-MIB'));
if (preg_match("/\b 00 00 00 00 00 00\b/i", $hwversion)) {  //convert potential hex reading to character
    str_replace(" 00", "", $hwversion);
     $hwversion = rtrim(hexbin($hwversion));
}

$swversion = str_replace(array("\r","\n"), '', snmp_get($device, 'msppDevSwVersion.0', '-Oqv', 'WRI-DEVICE-MIB'));
if (preg_match("/\b 00 00 00 00 00 00\b/i", $swversion)) {  //convert potential hex reading to character
    str_replace(" 00", "", $swversion);
     $swversion = rtrim(hexbin($swversion));
}

$hardware = 'FHN '.$sysDescrPieces[0].' V '.$hwversion;
$version  = $swversion;
$features = '';    // currently no features available
$serial   = 'NA';  // currently no HW serial number through MIB

// End of File
