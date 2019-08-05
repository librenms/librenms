<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 * Copyright (c) 2017 Tony Murray <https://github.com/murrant>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: text/plain');

// FUA

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

for ($x = 0; $x < count($_POST['sensor_id']); $x++) {
    dbUpdate(
        array(
            'sensor_limit' => set_null($_POST['sensor_limit'][$x], array('NULL')),
            'sensor_limit_low' => set_null($_POST['sensor_limit_low'][$x], array('NULL')),
            'sensor_alert' => set_null($_POST['sensor_alert'][$x], array('NULL'))
        ),
        'wireless_sensors',
        '`sensor_id` = ?',
        array($_POST['sensor_id'][$x])
    );
}
