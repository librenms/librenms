<?php

/*
 * LibreNMS OS Polling module for packetflux
 *
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Grab the coordinates read from GPS stats and update the location if different

    // PACKETFLUX-GNSS-MIB::gnssLatitude.0.0
    // PACKETFLUX-GNSS-MIB::gnssLongitude.0.0
    $oids = array('.1.3.6.1.4.1.32050.3.4.1.1.3.0.0', '.1.3.6.1.4.1.32050.3.4.1.1.4.0.0')
    $results = snmp_getnext_multi($device, $oids,'-OQv', 'PACKETFLUX-GNSS-MIB');

    $lat = str_replace(' degrees', '', $results[0]);
    $lng = str_replace(' degrees', '', $results[1]);

    $coord = "[".round($location->lat,3).", ".round($location->lng,3)."]";
    $newcoord = "[".round($lat,3).", ".round($lng,3)."]";

    // If the coordinates are different and the new values are populdated, update
    if (isset($location) and $coord != $newcoord and abs($lat)>0 and abs($lng)>0) {
        $location->lat = $lat;
        $location->lng = $lng;
        $location->save();
        log_event('Location Update '. $coord . ' -> ' . $newcoord.'('.$lat.','.$lng.')', $device, 'system', 3);
    }

    $serial = snmp_get($device, 'ifPhysAddress.1','-OQv', 'IF-MIB');

