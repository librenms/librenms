<?php
/**
 * LibreNMS - ADVA FSP150-XG210 (MetroE Subaggregation) device support
 *
 * @category Network_Management
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

if ($device['os'] == 'advafsp150ge20x') {
    echo 'Pre-cache ADVA FSP150 XG 210 (advafsp150ge20x):';
    echo "\n";

    $ge20x_oids = array();

    echo 'Caching OIDs:'."\n";

    $ge20x_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', $ge20x_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150ge20x', '-OQUbs');

    echo 'OIDs:'."\n";

    //var_dump($egx_oids);
    //$results = print_r($ge20x_oids, true); // $results now contains output from print_r
    //file_put_contents('/opt/librenms/adva-precache.txt', $results);
}

// end of OS condition
