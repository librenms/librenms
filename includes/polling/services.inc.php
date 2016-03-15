<?php

/*
 * LibreNMS module to poll Nagios Services
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

foreach (dbFetchRows('SELECT * FROM `devices` AS D, `services` AS S WHERE S.device_id = D.device_id AND D.device_id = ? ORDER by D.device_id DESC', array($device['device_id'])) as $service) {
    // Run the polling function
    service_poll($service);

} //end foreach
