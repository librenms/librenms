<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Steve Calvário <https://github.com/Calvario/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'eatonups') {
    echo 'Eaton UPS Charge';

    // Eaton UPS Battery Charge Level
    $ups_charge_oid = '.1.3.6.1.4.1.534.1.2.4.0';
    $ups_charge     = snmp_get($device, $ups_charge_oid, '-Oqv');

    if (is_numeric($ups_charge)) {
        discover_sensor($valid['sensor'], 'charge', $device, $ups_charge_oid, 'UPS Charge', $ups_device_manufacturer.' '.$ups_device_model, 'UPS Charge', '1', '1', null, null, null, null, $ups_charge);
    }

}
