<?php
/*
 * LibreNMS - ADVA FSP150-CC-GE11x (MetroE Edge) device support
 *
 * Copyright (c) 2017 Christoph Zilian <czilian@hotmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

    echo 'Pre-cache ADVA FSP150 GE 11x (advafsp150ge11x):';
    echo "\n";

    $ge11x_oids = array();

    echo 'Caching OIDs:'."\n";

    $ge11x_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects' , $ge11x_oids,   'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150ge11x', '-OQUbs');

    echo 'OIDs:'."\n";

//var_dump($ge11x_oids);
//$results = print_r($ge11x_oids, true); // $results now contains output from print_r
//file_put_contents('/opt/librenms/adva-precache.txt', $results);

// end of OS condition
