<?php
/*
 * LibreNMS - ADVA FSP150-EGX (MetroE Core Switch) device support
 *
 * Copyright (c) 2017 Christoph Zilian <czilian@hotmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


if ($device['os'] == 'advafsp150eg-x') {
    echo 'Pre-cache ADVA FSP150 EG-X (advafsp150eg-x):';
    echo "\n";

    $egx_oids = array();
    $egxPSU   = array();
    $egxSWF   = array();

    echo 'Caching OIDs:'."\n";

    $egxPSU   = snmpwalk_cache_multi_oid($device, 'psuTable'                     , $egxPSU,   'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egxSWF   = snmpwalk_cache_multi_oid($device, 'ethernetSWFCardTable'         , $egxSWF,   'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernetSWFCardTable'         , $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernet1x10GHighPerCardTable', $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernet10x1GHighPerCardTable', $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');

    echo 'OIDs:'."\n";

//var_dump($egx_oids);
//$results = print_r($egxSWF, true); // $results now contains output from print_r
//file_put_contents('/opt/librenms/adva-precache.txt', $results);

} // end of OS condition
