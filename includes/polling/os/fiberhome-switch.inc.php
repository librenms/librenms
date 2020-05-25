<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage FiberHome Switch Device Support - os module
 * @link       http://librenms.org
 * @copyright  2018 Christoph Zilian <czilian@hotmail.com>
 * @author     Christoph Zilian <czilian@hotmail.com>
*/

$sysDescrPieces = explode(" ", $device['sysDescr']); //extract model from sysDescr

$versions = snmp_get_multi_oid($device, ['msppDevHwVersion.0', 'msppDevSwVersion.0'], '-OQs', 'WRI-DEVICE-MIB');
foreach ($versions as $key => $field) {
    if (preg_match("/\b 00 00 00 00 00 00\b/i", $field)) {  //convert potential hex reading to character
        $versions[$key] = str_replace(array("\r","\n"), '', $field);
        $versions[$key] = str_replace(" 00", "", $field);
        $versions[$key] = rtrim(hexbin($field));
    }
}

$hardware = 'FHN '.$sysDescrPieces[0].' V '.$versions['msppDevHwVersion.0'];
$version  = $versions['msppDevSwVersion.0'];
$features = '';    // currently no features available
$serial   = '';  // currently no HW serial number through MIB
